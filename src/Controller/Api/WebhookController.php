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
        // x
        $authToken = $_ENV['AUTH_TOKEN_WEBHOOK']; 

        $signature = $request->headers->get('X-Hub-Signature-256');
        $body = $request->getContent();
        $calculatedSignature = 'sha256=' . hash_hmac('sha256', $body, $authToken);

        // if ($signature !== $calculatedSignature) {
        //     return new JsonResponse('Unauthorized request!', 401);
        // }
        // $stashProcess = new Process(['git', 'stash']);
        // $stashProcess->run();

        // if (!$stashProcess->isSuccessful()) {
        //     return new JsonResponse('Git stash failed', 500);
        // }

        $pullProcess = new Process(['git', 'pull', 'origin', 'main']);
        $pullProcess->run();
            return new JsonResponse('Git pull successful', 200);

        // if ($pullProcess->isSuccessful()) {
        //     return new JsonResponse('Git pull successful', 200);
        // } else {
        //     return new JsonResponse('Git pull failed', 500);
        // }

    }
}
