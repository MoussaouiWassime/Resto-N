<?php

namespace App\Controller;

use App\Repository\RestaurantRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ChatbotController extends AbstractController
{
    #[Route('/chatbot', name: 'app_chatbot', methods: ['POST'])]
    public function chat(
        Request $request,
        HttpClientInterface $httpClient,
        RestaurantRepository $restaurantRepo,
        HtmlSanitizerInterface $htmlSanitizer,
        LoggerInterface $logger,
        UrlGeneratorInterface $urlGenerator,
    ): JsonResponse {
        $rawMessage = $request->request->get('message', '');
        $cleanMessage = $htmlSanitizer->sanitizeFor('chatbot_sanitizer', $rawMessage);

        if (empty(trim($cleanMessage))) {
            return new JsonResponse(['reply' => 'Bonjour !']);
        }

        try {
            $filters = $this->extractFiltersWithAI($cleanMessage, $httpClient);
            $logger->info('Filtres extraits : ', $filters);
            $restaurants = $restaurantRepo->searchByCriteria($filters);
            $finalResponse = $this->generateFinalResponse($cleanMessage, $restaurants, $httpClient, $urlGenerator);

            return new JsonResponse(['reply' => $htmlSanitizer->sanitizeFor('chatbot_sanitizer', $finalResponse)]);
        } catch (\Exception $e) {
            $logger->error($e->getMessage());

            return new JsonResponse(['reply' => 'Désolé, une erreur technique est survenue.'], 500);
        }
    }

    private function extractFiltersWithAI(string $userMessage, HttpClientInterface $client): array
    {
        $prompt = "Tu es un extracteur de données. Analyse la phrase de l'utilisateur et extrais les critères de recherche de restaurant.\n";
        $prompt .= "Retourne UNIQUEMENT un objet JSON valide avec ces clés (laisse vide si non trouvé) : 'city', 'category', 'name'.\n";
        $prompt .= "Exemple Utilisateur : 'Je veux un italien à Fernandez'\n";
        $prompt .= "Exemple Réponse : {\"city\": \"Fernandez\", \"category\": \"Italien\", \"name\": \"\"}\n";
        $prompt .= 'Phrase à analyser : '.$userMessage;

        $response = $client->request('POST', 'https://api.mistral.ai/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer '.($_ENV['MISTRAL_API_KEY'] ?? ''),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'mistral-small-latest',
                'messages' => [['role' => 'user', 'content' => $prompt]],
                'temperature' => 0,
                'response_format' => ['type' => 'json_object'],
            ],
        ]);

        return json_decode($response->toArray()['choices'][0]['message']['content'], true) ?? [];
    }

    private function generateFinalResponse(
        string $userQuery,
        array $restaurants,
        HttpClientInterface $client,
        UrlGeneratorInterface $urlGenerator,
    ): string {
        $context = "Tu es l'assistant officiel de Resto'N. Ton but est de convertir la conversation en réservation ou en commande.\n";

        if (empty($restaurants)) {
            $context .= "Aucun résultat pour la recherche '$userQuery'. Excuse-toi poliment.";
        } else {
            $context .= "Voici les restaurants trouvés. Utilise ces liens EXACTS pour guider l'utilisateur :\n\n";

            foreach ($restaurants as $r) {
                $urlResa = $urlGenerator->generate(
                    'app_reservation_create',
                    ['id' => $r->getId()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $urlOrder = $urlGenerator->generate(
                    'app_order_create',
                    ['id' => $r->getId()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $cats = array_map(fn ($c) => $c->getName(), $r->getCategories()->toArray());

                $context .= sprintf(
                    "Resto: %s (Ville: %s, Style: %s)\n- Lien Réservation: %s\n- Lien Commande: %s\n\n",
                    $r->getName(),
                    $r->getCity(),
                    implode(', ', $cats),
                    $urlResa,
                    $urlOrder
                );
            }

            $context .= "CONSIGNES DE RÉPONSE :\n";
            $context .= "- Sois court et vendeur.\n";
            $context .= "- Si l'utilisateur veut manger sur place, donne-lui le lien de réservation.\n";
            $context .= "- Si l'utilisateur veut emporter, donne-lui le lien de commande.\n";
            $context .= "- IMPORTANT : Formate les liens en HTML pour qu'ils soient cliquables. Exemple : <a href='URL_ICI' target='_blank'>Réserver une table 📅</a>.";
        }

        return $this->callMistral($userQuery, $context, $client);
    }

    private function callMistral(string $msg, string $ctx, HttpClientInterface $httpClient): string
    {
        $apiKey = $_ENV['MISTRAL_API_KEY'] ?? '';

        if (empty($apiKey)) {
            throw new \Exception('Clé API manquante.');
        }

        $response = $httpClient->request('POST', 'https://api.mistral.ai/v1/chat/completions', [
            'headers' => ['Authorization' => 'Bearer '.$apiKey, 'Content-Type' => 'application/json'],
            'json' => [
                'model' => 'mistral-small-latest',
                'messages' => [['role' => 'system', 'content' => $ctx], ['role' => 'user', 'content' => $msg]],
                'temperature' => 0.7,
                'max_tokens' => 400,
            ],
            'timeout' => 10,
        ]);

        return $response->toArray()['choices'][0]['message']['content'] ?? '...';
    }
}
