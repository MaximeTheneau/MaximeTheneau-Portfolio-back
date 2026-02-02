<?php

namespace App\Service;

class BookingSlotService
{
    private $googleCalendarService;
    private $config;
    private $slotDuration;
    private $bufferMinutes;

    public function __construct(
        GoogleCalendarService $googleCalendarService,
        string $configPath
    ) {
        $this->googleCalendarService = $googleCalendarService;
        $this->loadConfig($configPath);
    }

    /**
     * Charge la configuration depuis le fichier JSON
     */
    private function loadConfig(string $configPath): void
    {
        if (!file_exists($configPath)) {
            throw new \Exception("Fichier de configuration non trouvé: {$configPath}");
        }

        $jsonContent = file_get_contents($configPath);
        $this->config = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Erreur lors du parsing du fichier de configuration: " . json_last_error_msg());
        }

        $this->slotDuration = $this->config['slot_duration_minutes'] ?? 30;
        $this->bufferMinutes = $this->config['buffer_minutes'] ?? 15;
    }

    /**
     * Récupère les créneaux disponibles pour une date donnée
     *
     * @param \DateTime $date Date pour laquelle récupérer les créneaux
     * @return array Liste des créneaux avec leur statut de disponibilité
     */
    public function getAvailableSlots(\DateTime $date): array
    {
        $dayOfWeek = strtolower($date->format('l'));
        $businessHours = $this->getBusinessHours($dayOfWeek);

        if (empty($businessHours) || !$businessHours['enabled']) {
            return [];
        }

        if ($this->isHoliday($date)) {
            return [];
        }

        $allSlots = $this->generateSlotsForDay($date, $businessHours['slots']);

        $startOfDay = clone $date;
        $startOfDay->setTime(0, 0, 0);
        $endOfDay = clone $date;
        $endOfDay->setTime(23, 59, 59);

        $events = $this->googleCalendarService->getEvents($startOfDay, $endOfDay);

        $availableSlots = $this->filterBookedSlots($allSlots, $events);

        return $availableSlots;
    }

    /**
     * Vérifie si un créneau spécifique est disponible
     *
     * @param \DateTime $startTime Date/heure du début du créneau
     * @return bool true si disponible, false sinon
     */
    public function isSlotAvailable(\DateTime $startTime): bool
    {
        $dayOfWeek = strtolower($startTime->format('l'));
        $businessHours = $this->getBusinessHours($dayOfWeek);

        if (empty($businessHours) || !$businessHours['enabled']) {
            return false;
        }

        if ($this->isHoliday($startTime)) {
            return false;
        }

        $timeStr = $startTime->format('H:i');
        $isWithinBusinessHours = false;

        foreach ($businessHours['slots'] as $slot) {
            if ($timeStr >= $slot['start'] && $timeStr < $slot['end']) {
                $isWithinBusinessHours = true;
                break;
            }
        }

        if (!$isWithinBusinessHours) {
            return false;
        }

        return $this->googleCalendarService->checkAvailability($startTime, $this->slotDuration);
    }

    /**
     * Récupère les horaires d'ouverture pour un jour de la semaine
     *
     * @param string $dayOfWeek Jour de la semaine (monday, tuesday, etc.)
     * @return array Configuration des horaires
     */
    public function getBusinessHours(string $dayOfWeek): array
    {
        return $this->config['business_hours'][$dayOfWeek] ?? ['enabled' => false, 'slots' => []];
    }

    /**
     * Vérifie si une date est un jour férié
     *
     * @param \DateTime $date Date à vérifier
     * @return bool true si jour férié, false sinon
     */
    public function isHoliday(\DateTime $date): bool
    {
        $dateStr = $date->format('Y-m-d');
        return in_array($dateStr, $this->config['holidays'] ?? []);
    }

    /**
     * Génère tous les créneaux théoriques pour une journée
     *
     * @param \DateTime $date Date du jour
     * @param array $businessSlots Plages horaires d'ouverture
     * @return array Liste des créneaux
     */
    private function generateSlotsForDay(\DateTime $date, array $businessSlots): array
    {
        $slots = [];

        foreach ($businessSlots as $businessSlot) {
            $currentTime = clone $date;
            list($startHour, $startMinute) = explode(':', $businessSlot['start']);
            $currentTime->setTime((int)$startHour, (int)$startMinute, 0);

            $endTime = clone $date;
            list($endHour, $endMinute) = explode(':', $businessSlot['end']);
            $endTime->setTime((int)$endHour, (int)$endMinute, 0);

            while ($currentTime < $endTime) {
                $slotEnd = clone $currentTime;
                $slotEnd->modify("+{$this->slotDuration} minutes");

                if ($slotEnd <= $endTime) {
                    $slots[] = [
                        'start' => clone $currentTime,
                        'end' => clone $slotEnd,
                        'available' => true,
                    ];
                }

                $currentTime->modify("+{$this->slotDuration} minutes");
                $currentTime->modify("+{$this->bufferMinutes} minutes");
            }
        }

        return $slots;
    }

    /**
     * Filtre les créneaux déjà réservés
     *
     * @param array $slots Tous les créneaux théoriques
     * @param array $events Événements du calendrier
     * @return array Créneaux avec statut de disponibilité
     */
    private function filterBookedSlots(array $slots, array $events): array
    {
        $filteredSlots = [];

        foreach ($slots as $slot) {
            $slotStart = $slot['start'];
            $slotEnd = $slot['end'];
            $available = true;

            foreach ($events as $event) {
                $eventStart = new \DateTime($event['start']);
                $eventEnd = new \DateTime($event['end']);

                $bufferStart = clone $eventStart;
                $bufferStart->modify("-{$this->bufferMinutes} minutes");
                $bufferEnd = clone $eventEnd;
                $bufferEnd->modify("+{$this->bufferMinutes} minutes");

                if ($slotStart < $bufferEnd && $slotEnd > $bufferStart) {
                    $available = false;
                    break;
                }
            }

            $now = new \DateTime();
            $minNotice = $_ENV['BOOKING_MIN_NOTICE_HOURS'] ?? 24;
            $minBookingTime = clone $now;
            $minBookingTime->modify("+{$minNotice} hours");

            if ($slotStart < $minBookingTime) {
                $available = false;
            }

            $filteredSlots[] = [
                'start' => $slotStart->format('Y-m-d\TH:i:s'),
                'end' => $slotEnd->format('Y-m-d\TH:i:s'),
                'available' => $available,
            ];
        }

        return $filteredSlots;
    }

    /**
     * Récupère la configuration complète
     *
     * @return array Configuration
     */
    public function getConfig(): array
    {
        return [
            'business_hours' => $this->config['business_hours'],
            'holidays' => $this->config['holidays'],
            'slot_duration_minutes' => $this->slotDuration,
            'buffer_minutes' => $this->bufferMinutes,
            'min_notice_hours' => $_ENV['BOOKING_MIN_NOTICE_HOURS'] ?? 24,
            'advance_days' => $_ENV['BOOKING_ADVANCE_DAYS'] ?? 30,
        ];
    }
}
