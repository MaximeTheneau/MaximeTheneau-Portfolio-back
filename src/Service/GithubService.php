<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class GithubService
{
    private $client;
    private $mailer;

    public function __construct(HttpClientInterface $client, MailerInterface $mailer)
    {
        $this->client = $client;
        $this->mailer = $mailer;
    }

    public function triggerWorkflow()
    {
        $authToken = $_ENV['DOMAIN_FRONTEND'];
        $eventData = ['x-github-event' => 'build'];
        $jsonData = json_encode($eventData);
        $hmacSignature = hash_hmac('sha256', $jsonData, $authToken);

        $url = 'http://' . $_ENV['DOMAIN_FRONTEND'] . '/api/webhook';

        try {
            $responseApi = $this->client->request('POST', $url, [
                'headers' => [
                    'X-Hub-Signature-256' => 'sha256=' . $hmacSignature,
                    'Accept' => 'application/json',
                    'x-github-event' => 'build'
                ],
                'json' => $eventData,
            ]);

            $statusCode = $responseApi->getStatusCode();

            if ($statusCode !== 200) {
                $this->sendErrorEmail($statusCode);
            }
        } catch (\Exception $e) {
            $this->sendErrorEmail($e->getMessage());
        }
    }

    private function sendErrorEmail($errorDetails)
    {
        $email = (new TemplatedEmail())
        ->to($_ENV['MAILER_FROM'])
        ->from($_ENV['MAILER_TO'])
        ->subject('Erreur lors de l\'envoie du build')
        ->htmlTemplate('emails/error.html.twig')
        ->context([
            'error' => $errorDetails,
        ]);
        $this->mailer->send($email);
    }
}

