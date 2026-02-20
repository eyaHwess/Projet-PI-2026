<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class TrelloService
{
    private HttpClientInterface $client;
    private string $apiKey;
    private string $apiToken;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
        $this->apiKey = $_ENV['TRELLO_API_KEY'];
        $this->apiToken = $_ENV['TRELLO_API_TOKEN'];
    }

    public function getBoards(): array
    {
        $response = $this->client->request('GET', 'https://api.trello.com/1/members/me/boards', [
            'query' => [
                'key' => $this->apiKey,
                'token' => $this->apiToken,
            ],
        ]);

        return $response->toArray();
    }

    public function getCardsFromBoard(string $boardId): array
    {
        $response = $this->client->request('GET', "https://api.trello.com/1/boards/{$boardId}/cards", [
            'query' => [
                'key' => $this->apiKey,
                'token' => $this->apiToken,
                'filter' => 'visible', // Only get visible cards (not archived)
            ],
        ]);

        return $response->toArray();
    }

    public function getCardsFromList(string $listId): array
    {
        $response = $this->client->request('GET', "https://api.trello.com/1/lists/{$listId}/cards", [
            'query' => [
                'key' => $this->apiKey,
                'token' => $this->apiToken,
                'filter' => 'open', // Only get open cards
            ],
        ]);

        return $response->toArray();
    }
    public function getListsFromBoard(string $boardId): array
{
    $response = $this->client->request('GET', "https://api.trello.com/1/boards/{$boardId}/lists", [
        'query' => [
            'key' => $this->apiKey,
            'token' => $this->apiToken,
        ],
    ]);

    return $response->toArray();
}
    
}