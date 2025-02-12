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
use Symfony\Component\Validator\Constraints as Assert;


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
    $data = $request->request->all();

    $imagePath = null;
    $uploadedFile = $request->files->get('image');

    if ($uploadedFile) {
        $mimeType = $uploadedFile->getMimeType();
        if (strpos($mimeType, 'image/') === 0) {
            $imagePath = $uploadedFile->getPathname();
            $fileName = uniqid() . '.' . $uploadedFile->guessExtension();
            $uploadedFile->move(
                $this->getParameter('app.imgDir'),
                $fileName
            );
            $imagePath =  'https://back.unetaupechezvous.fr/upload/img/' . $fileName;
        } else {
            // Ce n'est pas une image, renvoyez une erreur de format d'image
            return $this->json(
                [
                    "erreur" => "Le fichier téléchargé n'est pas une image",
                    "code_error" => Response::HTTP_FORBIDDEN
                ],
                Response::HTTP_FORBIDDEN
            );
        }
    }

    // if (strlen($data['postalCode']) !== 5) {
    //     return $this->json(
    //         [
    //             "erreur" => "Erreur lors de la saisie du code postal (5 chiffres attendus)",
    //             "code_error" => Response::HTTP_FORBIDDEN
    //         ],
    //         Response::HTTP_FORBIDDEN
    //     );
    // }

    // if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    //     return $this->json(
    //         [
    //             "erreur" => "Adresse e-mail invalide",
    //             "code_error" => 400
    //         ],
    //         Response::HTTP_BAD_REQUEST, // 400
    //     );
    // }
    try {

        if (isset($data['emailReturn']) && $data['emailReturn'] === 'true') {
            $emailReturn = (new TemplatedEmail())
            ->to($data['email'])
            ->from($_ENV['MAILER_TO'])
            ->subject('Votre message a bien été envoyé')
            ->htmlTemplate('emails/contactReturn.html.twig')
            ->context([
                'subjectContact' => $data['subject'],
                'nameContact' => $data['name'],
                'emailContact' => $data['email'],
                'phoneContact' => $data['phone'],
                'postalCodeContact' => $data['postalCode'],
                'messageContact' => $data['message'] ?? null,
                'imageContact' =>  $imagePath ?? null,
                'dateContact' => $data['date'] ?? null,
                'statusContact' => $data['status'] ?? null,
                'nameSocietyContact' => $data['nameSociety'] ?? null,
                'siretContact' => $data['siret'] ?? null,
                'adressContact' => $data['adress'] ?? null,
                'surfaceContact' => $data['surface'] ?? null ,
                'interventionContact' => $data['intervention'] ?? null,
                'interventionOtherContact' => $data['interventionOther'] ?? null,
            ]);

            $mailer->send($emailReturn);
            
        }

        if ($data['subject'] === 'Webmaster'  ) {
            $data['subject'] = 'Demande de contact webmaster';
            $emailTo = $_ENV['MAILER_TO_WEBMASTER'];
        }
        else {
            $emailTo = $_ENV['MAILER_TO'];
        }

        $email = (new TemplatedEmail())
            ->to($emailTo)
            ->from($_ENV['MAILER_TO'])
            ->subject($data['subject'] . ' de ' . $data['name'])
            ->htmlTemplate('emails/contact.html.twig')
            ->context([
                'subjectContact' => $data['subject'],
                'nameContact' => $data['name'],
                'emailContact' => $data['email'],
                'phoneContact' => $data['phone'],
                'postalCodeContact' => $data['postalCode'],
                'messageContact' => $data['message'] ?? null,
                'imageContact' =>  $imagePath ?? null,
                'dateContact' => $data['date'] ?? null,
                'statusContact' => $data['status'] ?? null,
                'nameSocietyContact' => $data['nameSociety'] ?? null,
                'siretContact' => $data['siret'] ?? null,
                'adressContact' => $data['adress'] ?? null,
                'surfaceContact' => $data['surface'] ?? null ,
                'interventionContact' => $data['intervention'] ?? null,
                'interventionOtherContact' => $data['interventionOther'] ?? null,
                
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
    catch (\Exception $e) {
        $email = (new TemplatedEmail())
        ->to($_ENV['MAILER_TO'])
        ->from($_ENV['MAILER_TO'])
        ->subject('Erreur lors de l\'envoie de l\'email')
        ->htmlTemplate('emails/error.html.twig')
        ->context([
            'error' => $e->getMessage(),
        ]);
        $mailer->send($email);


        return $this->json(
            [
                "message" => "Erreur lors de l'envoie de l'email, veuillez réessayer plus tard",
                "code_error" => Response::HTTP_FORBIDDEN
            ],
            Response::HTTP_FORBIDDEN
        );
    }
    }
            
    
}