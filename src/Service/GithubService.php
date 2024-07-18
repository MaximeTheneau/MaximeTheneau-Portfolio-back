<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GitHubService
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function triggerWorkflow()
    {

        $repoOwner = $_ENV['APP_FRONTEND_GITHUB_OWNER'];
        $repoName = $_ENV['APP_FRONTEND_GITHUB_REPO'];
        $eventType = 'Build';

        $url = sprintf('https://api.github.com/repos/%s/%s/dispatches', $repoOwner, $repoName);
        
        $response = $this->client->request('POST', $url, [
            'headers' => [
                'X-Hub-Signature-256' => 'sha256= ' . $_ENV['AUTH_TOKEN'],
                'Accept' => 'application/vnd.github.v3+json',
            ],
            'json' => [
                'event_type' => $eventType,
            ],
        ]);

        return $response->getStatusCode() === 204;
    }
}
