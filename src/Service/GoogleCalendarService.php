<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class GoogleCalendarService
{
    private $client;
    private $service;
    private $calendarId;
    private $mailer;

    public function __construct(
        string $calendarId,
        string $credentialsPath,
        MailerInterface $mailer
    ) {
        $this->calendarId = $calendarId;
        $this->mailer = $mailer;
        $this->authenticate($credentialsPath);
    }

    /**
     * Authentification avec Google Calendar API via Service Account
     */
    private function authenticate(string $credentialsPath): void
    {
        try {
            $this->client = new \Google_Client();
            $this->client->setApplicationName('Booking System');
            $this->client->setScopes([\Google_Service_Calendar::CALENDAR]);
            $this->client->setAuthConfig($credentialsPath);

            $this->service = new \Google_Service_Calendar($this->client);
        } catch (\Exception $e) {
            $this->sendErrorEmail('Erreur d\'authentification Google Calendar: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Récupère les événements d'une période donnée
     *
     * @param \DateTime $start Date de début
     * @param \DateTime $end Date de fin
     * @return array Liste des événements
     */
    public function getEvents(\DateTime $start, \DateTime $end): array
    {
        try {
            $optParams = [
                'timeMin' => $start->format(\DateTime::RFC3339),
                'timeMax' => $end->format(\DateTime::RFC3339),
                'singleEvents' => true,
                'orderBy' => 'startTime',
            ];

            $results = $this->service->events->listEvents($this->calendarId, $optParams);
            $events = $results->getItems();

            $formattedEvents = [];
            foreach ($events as $event) {
                $formattedEvents[] = [
                    'id' => $event->getId(),
                    'summary' => $event->getSummary(),
                    'start' => $event->getStart()->getDateTime() ?: $event->getStart()->getDate(),
                    'end' => $event->getEnd()->getDateTime() ?: $event->getEnd()->getDate(),
                ];
            }

            return $formattedEvents;
        } catch (\Exception $e) {
            $this->sendErrorEmail('Erreur lors de la récupération des événements: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Crée un nouvel événement dans Google Calendar
     *
     * @param string $summary Titre de l'événement
     * @param \DateTime $start Date/heure de début
     * @param \DateTime $end Date/heure de fin
     * @param array $attendeeData Données du participant (name, email, phone)
     * @return string ID de l'événement créé
     */
    public function createEvent(
        string $summary,
        \DateTime $start,
        \DateTime $end,
        array $attendeeData
    ): string {
        try {
            $event = new \Google_Service_Calendar_Event([
                'summary' => $summary,
                'description' => $this->formatEventDescription($attendeeData),
                'start' => [
                    'dateTime' => $start->format(\DateTime::RFC3339),
                    'timeZone' => 'Europe/Paris',
                ],
                'end' => [
                    'dateTime' => $end->format(\DateTime::RFC3339),
                    'timeZone' => 'Europe/Paris',
                ],
                'reminders' => [
                    'useDefault' => false,
                    'overrides' => [
                        ['method' => 'popup', 'minutes' => 60],
                    ],
                ],
            ]);

            $createdEvent = $this->service->events->insert($this->calendarId, $event);

            // Envoyer notification par email
            $this->sendBookingNotification($start, $attendeeData);

            return $createdEvent->getId();
        } catch (\Exception $e) {
            $this->sendErrorEmail('Erreur lors de la création de l\'événement: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Vérifie si un créneau est disponible
     *
     * @param \DateTime $dateTime Date/heure du créneau
     * @param int $durationMinutes Durée en minutes
     * @return bool true si disponible, false sinon
     */
    public function checkAvailability(\DateTime $dateTime, int $durationMinutes): bool
    {
        try {
            $endDateTime = clone $dateTime;
            $endDateTime->modify("+{$durationMinutes} minutes");

            $optParams = [
                'timeMin' => $dateTime->format(\DateTime::RFC3339),
                'timeMax' => $endDateTime->format(\DateTime::RFC3339),
                'singleEvents' => true,
            ];

            $results = $this->service->events->listEvents($this->calendarId, $optParams);
            $events = $results->getItems();

            return count($events) === 0;
        } catch (\Exception $e) {
            $this->sendErrorEmail('Erreur lors de la vérification de disponibilité: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Formate la description de l'événement avec les informations du participant
     */
    private function formatEventDescription(array $attendeeData): string
    {
        $description = "Rendez-vous téléphonique\n\n";
        $description .= "Nom: " . ($attendeeData['name'] ?? 'N/A') . "\n";
        $description .= "Email: " . ($attendeeData['email'] ?? 'N/A') . "\n";
        $description .= "Téléphone: " . ($attendeeData['phone'] ?? 'N/A') . "\n";

        if (!empty($attendeeData['message'])) {
            $description .= "\nMessage:\n" . $attendeeData['message'];
        }

        return $description;
    }

    /**
     * Envoie un email de notification pour une nouvelle réservation
     */
    private function sendBookingNotification(\DateTime $dateTime, array $attendeeData): void
    {
        try {
            $email = (new TemplatedEmail())
                ->to($_ENV['MAILER_TO'])
                ->from($_ENV['MAILER_TO'])
                ->subject('Nouvelle réservation - ' . $dateTime->format('d/m/Y H:i'))
                ->htmlTemplate('emails/booking_notification.html.twig')
                ->context([
                    'datetime' => $dateTime,
                    'name' => $attendeeData['name'] ?? 'N/A',
                    'email' => $attendeeData['email'] ?? 'N/A',
                    'phone' => $attendeeData['phone'] ?? 'N/A',
                    'message' => $attendeeData['message'] ?? '',
                ]);

            $this->mailer->send($email);
        } catch (\Exception $e) {
            error_log('Impossible d\'envoyer l\'email de notification: ' . $e->getMessage());
        }
    }

    /**
     * Envoie un email d'erreur à l'administrateur
     */
    private function sendErrorEmail(string $errorDetails): void
    {
        try {
            $email = (new TemplatedEmail())
                ->to($_ENV['MAILER_TO'])
                ->from($_ENV['MAILER_TO'])
                ->subject('Erreur Google Calendar - Système de réservation')
                ->htmlTemplate('emails/error.html.twig')
                ->context([
                    'error' => $errorDetails,
                ]);

            $this->mailer->send($email);
        } catch (\Exception $e) {
            error_log('Impossible d\'envoyer l\'email d\'erreur: ' . $e->getMessage());
        }
    }
}
