<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MistralAiService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        #[Autowire('%mistral_api_key%')] private string $apiKey,
    ) {
    }

    public function getChatCompletion(string $systemPrompt, string $userMessage, bool $jsonMode = false): string
    {
        $options = [
            'headers' => [
                'Authorization' => 'Bearer '.$this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'mistral-small-latest',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userMessage],
                ],
                'temperature' => 0.7,
            ],
        ];

        if ($jsonMode) {
            $options['json']['response_format'] = ['type' => 'json_object'];
            $options['json']['temperature'] = 0;
        }

        $response = $this->httpClient->request('POST', 'https://api.mistral.ai/v1/chat/completions', $options);

        try {
            $content = $response->toArray()['choices'][0]['message']['content'] ?? '';

            return $content;
        } catch (\Exception $e) {
            return '';
        }
    }
}
