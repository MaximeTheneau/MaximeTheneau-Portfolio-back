<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints\Email as EmailConstraint;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\NamedAddress;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

#[Route('/api/contact')]
class ContactController extends ApiController
{    
    private $tokenService;
    private $serializer;

    public function __construct(
        TokenStorageInterface $token,
        SerializerInterface $serializer,
    ) {
        $this->tokenService = $token;
        $this->serializer = $serializer;
    }
	
        #[Route('', name: 'add_contact', methods: ['POST'])]
        public function add(Request $request, MailerInterface $mailer): JsonResponse
        {

        $content = $request->getContent();
        $data = json_decode($content, true);

        try {
            

            $email = (new TemplatedEmail())
                ->to($_ENV['MAILER_FROM'])
                ->from($data['email'])
                ->subject('nouveau message de ' . $data['name'])
                ->htmlTemplate('emails/contact.html.twig')
                ->context([
                    'emailContact' => $data['email'],
                    'subjectContact' => $data['subject'],
                    'nameContact' => $data['name'],
                    'messageContact' => $data['message'],
                ])
                ->replyTo($data['email']);
    
            $mailer->send($email);
    
            return $this->json(
                [
                    "message" => "Votre message a bien été envoyé",
                ],
                Response::HTTP_OK,
            );

        } catch (\Exception $e) {
            $email = (new TemplatedEmail())
            ->to($_ENV['MAILER_FROM'])
            ->from($_ENV['MAILER_TO'])
            ->subject('Erreur lors de l\'envoie de l\'email')
            ->htmlTemplate('emails/error.html.twig')
            ->context([
                'error' => $e->getMessage(),
            ]);
            $mailer->send($email);
            
            
            return $this->json(
                [
                    "message" => "Une erreur est survenue lors de l'envoie de votre message. Veuillez réessayer plus tard.",
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }
    #[Route('&directory', name: 'add_contact_directory', methods: ['POST'])]
    public function addDirectory(Request $request, MailerInterface $mailer, ValidatorInterface $validator): JsonResponse
    {

    $content = $request->getContent();
    $data = json_decode($content, true);

    $constraintViolationList = $validator->validate($data['siteWeb'], [
        new Assert\Url(),
    ]);

    $constraintViolationList = $validator->validate($data['siteWeb'], [
        new Assert\Regex([
            'pattern' => '/^(https:\/\/)/',
            'message' => "Le site web doit commencer par 'https://'",
        ]),
    ]);


    if (
        empty($data['name']) ||
        empty($data['email']) ||
        empty($data['location']) ||
        empty($data['postalCode']) ||
        empty($data['siteWeb']) ||
        empty($data['service']) ||
        empty($data['subject']) ||
        empty($data['directory'])
        ) {
        return $this->json(
            [
                "erreur" => "Erreur de saisie",
                "code_error" => 404
            ],
            Response::HTTP_NOT_FOUND, // 404
        );
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        return $this->json(
            [
                "erreur" => "Adresse e-mail invalide",
                "code_error" => 400
            ],
            Response::HTTP_BAD_REQUEST, // 400
        );
    }
    
    if ($data['subject'] === 'Webmaster') {
        $data['subject'] = 'Demande de contact webmaster';
        $emailTo = $_ENV['MAILER_TO_WEBMASTER'];
    }
    else {
        return $this->json(
            [
                "erreur" => "Erreur de saisie",
                "code_error" => 400
            ],
            Response::HTTP_BAD_REQUEST, // 400
        );
    }
    $email = (new TemplatedEmail())
        ->to($emailTo)
        ->from($_ENV['MAILER_TO'])
        ->subject($data['subject'] . ' de ' . $data['name'])
        ->htmlTemplate('emails/contactDirectory.html.twig')
        ->context([
            'emailContact' => $data['email'],
            'subjectContact' => $data['subject'],
            'nameContact' => $data['name'],
            'postalCodeContact' => $data['postalCode'],
            'locationContact' => $data['location'],
            'websiteContact' => $data['siteWeb'],
            'serviceContact' => $data['service'],
            'directoryContact' => $data['directory'],
            'directoryOtherContact' => $data['directoryOther'] ?? null,

        ])
        ->replyTo($data['email']);

    $mailer->send($email);

    return $this->json(
        [
            "message" => "Votre message a bien été envoyé",
        ],
        Response::HTTP_OK,
    );
    }   

}