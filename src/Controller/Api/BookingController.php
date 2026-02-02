<?php

namespace App\Controller\Api;

use App\Service\BookingSlotService;
use App\Service\GoogleCalendarService;
use App\DTO\BookingRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

#[Route('/api/booking')]
class BookingController extends ApiController
{
    private $bookingSlotService;
    private $googleCalendarService;

    public function __construct(
        BookingSlotService $bookingSlotService,
        GoogleCalendarService $googleCalendarService
    ) {
        $this->bookingSlotService = $bookingSlotService;
        $this->googleCalendarService = $googleCalendarService;
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
