<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class RawgService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.rawg.io/api';
    protected $filterVersion = 'safe_v2';
    protected $blockedKeywords = [
        'adult', 'adults only', 'sexual', 'sex', 'erotic', 'hentai', 'porn', 'nsfw',
        'nudity', 'explicit', '18+', 'fetish', 'tentaclevan', 'tentacle', 'van'
    ];

    public function __construct()
    {
        $this->apiKey = config('services.rawg.key');
    }

    public function searchGames($query, $page = 1, $pageSize = 10)
    {
        $cacheKey = "rawg_search_{$this->filterVersion}_{$query}_{$page}_{$pageSize}";
        
        return Cache::remember($cacheKey, 3600, function () use ($query, $page, $pageSize) {
            $response = Http::get("{$this->baseUrl}/games", [
                'key' => $this->apiKey,
                'search' => $query,
                'page' => $page,
                'page_size' => $pageSize,
            ]);

            if ($response->successful()) {
                return $this->filterRawgResponse($response->json());
            }

            return ['results' => []];
        });
    }

    public function getGameDetails($gameId)
    {
        $cacheKey = "rawg_game_{$this->filterVersion}_{$gameId}";
        
        return Cache::remember($cacheKey, 86400, function () use ($gameId) {
            $response = Http::get("{$this->baseUrl}/games/{$gameId}", [
                'key' => $this->apiKey,
            ]);

            if ($response->successful()) {
                return $this->filterRawgResponse($response->json());
            }

            return null;
        });
    }

    public function getPopularGames($page = 1, $pageSize = 20)
    {
        $cacheKey = "rawg_popular_{$this->filterVersion}_{$page}_{$pageSize}";
        
        return Cache::remember($cacheKey, 3600, function () use ($page, $pageSize) {
            $response = Http::get("{$this->baseUrl}/games", [
                'key' => $this->apiKey,
                'page' => $page,
                'page_size' => $pageSize,
                'ordering' => '-rating',
            ]);

            if ($response->successful()) {
                return $this->filterRawgResponse($response->json());
            }

            return ['results' => []];
        });
    }

    public function getGamesByIds($gameIds)
    {
        if (empty($gameIds)) {
            return [];
        }

        $games = [];
        foreach ($gameIds as $id) {
            $game = $this->getGameDetails($id);
            if ($game) {
                $games[] = $game;
            }
        }

        return $games;
    }

    /**
     * Filtra resultados de la API para eliminar juegos con contenido sexual explícito.
     */
    protected function filterRawgResponse(?array $payload): array
    {
        if(!$payload) {
            return ['results' => []];
        }

        if(isset($payload['results']) && is_array($payload['results'])) {
            $payload['results'] = array_values(array_filter(
                $payload['results'],
                fn($game) => $this->isSafeGame($game)
            ));
        }

        return $payload;
    }

    /**
     * Determina si un juego contiene contenido vetado.
     */
    protected function isSafeGame(array $game): bool
    {
        $haystack = [];

        foreach (['name', 'slug', 'description', 'description_raw'] as $field) {
            if(!empty($game[$field])) {
                $haystack[] = strtolower($game[$field]);
            }
        }

        foreach (['tags', 'genres', 'themes'] as $relation) {
            if(!empty($game[$relation]) && is_array($game[$relation])) {
                foreach ($game[$relation] as $item) {
                    if(!empty($item['name'])) {
                        $haystack[] = strtolower($item['name']);
                    }
                    if(!empty($item['slug'])) {
                        $haystack[] = strtolower($item['slug']);
                    }
                }
            }
        }

        if(!empty($game['esrb_rating']['name'])) {
            $rating = strtolower($game['esrb_rating']['name']);
            if($rating === 'adults only' || $rating === 'ao') {
                return false;
            }
        }

        $combined = implode(' ', $haystack);

        foreach ($this->blockedKeywords as $keyword) {
            if(str_contains($combined, $keyword)) {
                return false;
            }
        }

        return true;
    }
}

