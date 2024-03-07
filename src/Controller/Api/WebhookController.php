<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Process\Process;

/**
 * @Route("/api")
 */
class WebhookController extends ApiController
{
    #[Route('/webhook/github', name: 'webhook_github', methods: ["get", "post"])]
    public function handleWebhook(Request $request): JsonResponse
{
    $payload = json_decode($request->getContent(), true);

    $signature = $request->headers->get('X-Hub-Signature');
    $secret = $_ENV['AUTH_TOKEN_WEBHOOK']; 
    $expectedSignature = 'sha1=' . hash_hmac('sha1', $request->getContent(), $secret);
    
    $projectDir = $this->getParameter('kernel.project_dir');
    $process = new Process(['git', 'pull']);
    $process->run();

    return new JsonResponse(['message' => 'Git pull réussi '. $process->getErrorOutput()], 200);
}
}