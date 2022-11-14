<?php

namespace App\Controller\Api;

use App\Entity\Message;
use App\Repository\MessageRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;


/**
 * @Route("/api/message",name="api_message_")
 */
class MessageController extends ApiController
{
    private $serializer;
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
    /**
     * @Route("", name="browse", methods={"POST"})
     */
    public function browse(MessageRepository $messageRepository, Request $request, MailerInterface $mailer): Response
    {
        
        
        $data = $request->getContent();
        #$data = json_decode($data, true);
        $message = $this->serializer->deserialize($data, Message::class, 'json');
        # dd($message);
        $email = (new Email())
        # Change 'example' to your own email address
            ->to('example@example.com')
            ->from($message->getEmail())
            ->subject($message->getSubject())
            ->text($message->getMessage())
            ->html('<p> Name :'.$message->getName().' email :'.$message->getEmail().'</p><p>'.$message->getMessage().'</p>');
        $mailer->send($email);
        #dd($email);
        $messageRepository->add($message, true);
        return $this->json($message, 201, []);
    }

   

}
