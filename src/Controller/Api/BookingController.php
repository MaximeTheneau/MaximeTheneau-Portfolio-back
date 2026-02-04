<?php

namespace App\Controller\Api;

use App\Service\BookingSlotService;
use App\Service\GoogleCalendarService;
use App\DTO\BookingRequest;
use App\Entity\BookingAttempt;
use App\Repository\BookingAttemptRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

#[Route('/api/booking')]
class BookingController extends ApiController
{
    private const MAX_BOOKINGS_PER_IP = 2;
    private const RATE_LIMIT_DAYS = 30;

    private $bookingSlotService;
    private $googleCalendarService;
    private $bookingAttemptRepository;
    private $entityManager;

    public function __construct(
        BookingSlotService $bookingSlotService,
        GoogleCalendarService $googleCalendarService,
        BookingAttemptRepository $bookingAttemptRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->bookingSlotService = $bookingSlotService;
        $this->googleCalendarService = $googleCalendarService;
        $this->bookingAttemptRepository = $bookingAttemptRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Récupère les créneaux disponibles pour une date
     * GET /api/booking/slots?date=2026-02-15
     */
    #[Route('/slots', name: 'booking_slots', methods: ['GET'])]
    public function getSlots(Request $request): JsonResponse
    {
        try {
            $dateStr = $request->query->get('date');

            if (empty($dateStr)) {
                return $this->json([
                    'erreur' => 'Le paramètre date est requis',
                    'code_error' => Response::HTTP_BAD_REQUEST
                ], Response::HTTP_BAD_REQUEST);
            }

            $date = \DateTime::createFromFormat('Y-m-d', $dateStr);

            if (!$date) {
                return $this->json([
                    'erreur' => 'Format de date invalide. Utilisez YYYY-MM-DD',
                    'code_error' => Response::HTTP_BAD_REQUEST
                ], Response::HTTP_BAD_REQUEST);
            }

            $date->setTime(0, 0, 0);

            $slots = $this->bookingSlotService->getAvailableSlots($date);

            return $this->json([
                'slots' => $slots
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->json([
                'erreur' => 'Erreur lors de la récupération des créneaux',
                'code_error' => Response::HTTP_INTERNAL_SERVER_ERROR
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Crée une nouvelle réservation
     * POST /api/booking/create
     */
    #[Route('/create', name: 'booking_create', methods: ['POST'])]
    public function create(Request $request, MailerInterface $mailer): JsonResponse
    {
        try {
            $data = $request->request->all();
            $bookingRequest = BookingRequest::fromArray($data);
            $clientIp = $request->getClientIp() ?? 'unknown';
            $adminEmail = $_ENV['MAILER_TO'] ?? '';
            $adminIp = $_ENV['ADMIN_IP'] ?? '88.138.139.74';

            // Exclure l'email admin et l'IP admin du rate limiting
            $isAdmin = (!empty($adminEmail) && strtolower($bookingRequest->email) === strtolower($adminEmail))
                || $clientIp === $adminIp;

            if (!$isAdmin) {
                // Vérification du rate limit par IP
                $bookingCount = $this->bookingAttemptRepository->countByIpSince($clientIp, self::RATE_LIMIT_DAYS);

                if ($bookingCount >= self::MAX_BOOKINGS_PER_IP) {
                    return $this->json([
                        'erreur' => 'Vous avez atteint le nombre maximum de réservations autorisées (' . self::MAX_BOOKINGS_PER_IP . '). Veuillez nous contacter directement pour plus de rendez-vous.',
                        'code_error' => Response::HTTP_TOO_MANY_REQUESTS
                    ], Response::HTTP_TOO_MANY_REQUESTS);
                }
            }

            $validationErrors = $bookingRequest->validate();
            if (!empty($validationErrors)) {
                return $this->json([
                    'erreur' => 'Veuillez renseigner correctement tous les champs',
                    'details' => $validationErrors,
                    'code_error' => Response::HTTP_BAD_REQUEST
                ], Response::HTTP_BAD_REQUEST);
            }

            $isAvailable = $this->bookingSlotService->isSlotAvailable($bookingRequest->dateTime);

            if (!$isAvailable) {
                return $this->json([
                    'erreur' => 'Ce créneau n\'est plus disponible. Veuillez en sélectionner un autre.',
                    'code_error' => Response::HTTP_CONFLICT
                ], Response::HTTP_CONFLICT);
            }

            $slotDuration = $_ENV['BOOKING_SLOT_DURATION'] ?? 30;
            $endDateTime = clone $bookingRequest->dateTime;
            $endDateTime->modify("+{$slotDuration} minutes");

            $summary = 'Rendez-vous téléphonique - ' . $bookingRequest->name;
            $attendeeData = $bookingRequest->toArray();

            $eventId = $this->googleCalendarService->createEvent(
                $summary,
                $bookingRequest->dateTime,
                $endDateTime,
                $attendeeData
            );

            // Enregistrer la tentative de réservation réussie
            $bookingAttempt = new BookingAttempt();
            $bookingAttempt->setIpAddress($clientIp);
            $bookingAttempt->setEmail($bookingRequest->email);
            $bookingAttempt->setPhone($bookingRequest->phone);
            $this->entityManager->persist($bookingAttempt);
            $this->entityManager->flush();

            return $this->json([
                'message' => 'Votre rendez-vous a été réservé avec succès',
                'event_id' => $eventId,
                'datetime' => $bookingRequest->dateTime->format('Y-m-d H:i')
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            $this->sendErrorEmail($mailer, $e->getMessage());

            return $this->json([
                'erreur' => 'Erreur lors de la réservation. Veuillez réessayer plus tard.',
                'code_error' => Response::HTTP_INTERNAL_SERVER_ERROR
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Récupère la configuration des réservations
     * GET /api/booking/config
     */
    #[Route('/config', name: 'booking_config', methods: ['GET'])]
    public function getConfig(): JsonResponse
    {
        try {
            $config = $this->bookingSlotService->getConfig();

            return $this->json($config, Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->json([
                'erreur' => 'Erreur lors de la récupération de la configuration',
                'code_error' => Response::HTTP_INTERNAL_SERVER_ERROR
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Envoie un email d'erreur à l'administrateur
     */
    private function sendErrorEmail(MailerInterface $mailer, string $errorDetails): void
    {
        try {
            $email = (new TemplatedEmail())
                ->to($_ENV['MAILER_TO'])
                ->from($_ENV['MAILER_TO'])
                ->subject('Erreur système de réservation')
                ->htmlTemplate('emails/error.html.twig')
                ->context([
                    'error' => $errorDetails,
                ]);

            $mailer->send($email);
        } catch (\Exception $e) {
            error_log('Impossible d\'envoyer l\'email d\'erreur: ' . $e->getMessage());
        }
    }
}
