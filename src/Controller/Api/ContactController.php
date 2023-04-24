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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\NamedAddress;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
/**
 * @Route("/api/contact")
 */
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
	
        /**
         * @Route("", name="add_contact", methods={"POST"})
         */
        public function add(Request $request, MailerInterface $mailer): JsonResponse
        {

        $content = $request->getContent();
        $data = json_decode($content, true);

        if (empty($data['name'] || $data['email'] || $data['message'])) {
            return $this->json(
                [
                    "erreur" => "Erreur de saisie",
                    "code_error" => 404
                ],
                Response::HTTP_NOT_FOUND,// 404
            );
        }

        $email = (new TemplatedEmail())
            ->to($_ENV['MAILER_TO'])
            ->from($_ENV['MAILER_FROM'])
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
        }
	
}