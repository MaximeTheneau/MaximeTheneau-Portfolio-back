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
    /**
     * @Route("/webhook/github", name="webhook_github", methods={"POST"})
     */
    public function handleWebhook(Request $request): JsonResponse
{
    // test ddd
    $payload = json_decode($request->getContent(), true);

    // Vérifiez si le webhook provient de GitHub
    $signature = $request->headers->get('X-Hub-Signature');
    $secret = $_ENV['AUTH_TOKEN_WEBHOOK']; // Remplacez par votre secret GitHub
    $expectedSignature = 'sha1=' . hash_hmac('sha1', $request->getContent(), $secret);
    
    // if ($signature !== $expectedSignature) {
    //     return new JsonResponse(['message' => 'Signature invalide'], 403);
    // } 


    // Assurez-vous que le webhook concerne un push vers la branche souhaitée
    // if ($payload['ref'] !== 'refs/heads/votre_branche') {
    //     return new JsonResponse(['message' => 'Webhook non pertinent'], 400);
    // } 
   
    // Exécutez un 'git pull' dans le répertoire de votre projet
    $projectDir = $this->getParameter('kernel.project_dir');
    dd($projectDir);
    $process = new Process(['git', 'pull']);
    $process->setWorkingDirectory($projectDir);
    $process->run();
 
    // if (!$process->isSuccessful()) {
    //     return new JsonResponse(['message' => 'Erreur lors de l\'exécution de git pull'], 500);
    // }

    return new JsonResponse(['message' => 'Git pull réussi '. $process->getErrorOutput()], 200);
}
}