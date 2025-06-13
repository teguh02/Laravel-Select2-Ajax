<?php

namespace TeguhRijanandi\LaravelSelect2Ajax\Http\Controllers;

use App\Http\Controllers\Controller;
use TeguhRijanandi\LaravelSelect2Ajax\Http\Requests\SearchRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;

class Select2Controller extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(SearchRequest $request): JsonResponse
    {
        $config = config('select2-ajax.query');
        $validated = $request->validated();
        $q = $validated['q'];
        $query = $validated['query'];

        try {
            $queryKeys = array_keys($config);
            $queryNames = array_map(fn($k) => Str::afterLast($k, '\\'), $queryKeys);
            $cacheTtl = config('select2-ajax.cache_ttl', 0);

            if (!in_array($query, $queryNames)) {
                return response()->json(['error' => 'Invalid query type provided.'], 400);
            }

            $index = array_search($query, $queryNames);
            $models = $queryKeys[$index];
            $details = $config[$models] ?? null;

            if (!$details) {
                return response()->json(['error' => "Query configuration for {$query} not found."], 404);
            }

            // Check if id and text fields are set
            if (!isset($details['id']) || !isset($details['text'])) {
                return response()->json(['error' => "ID and text fields for {$query} are not properly configured."], 500);
            }

            // Check if searchable is null or not set, then throw an error
            if (!isset($details['searchable']) || !is_array($details['searchable'])) {
                return response()->json(['error' => "Searchable fields for {$query} are not properly configured."], 500);
            }

            $queryBuilder = function () use ($models, $details, $q) {
                return app($models)::query()
                ->selectRaw("{$details['id']} as id, {$details['text']} as text")
                ->when(isset($details['where']) && filled($details['where']) && is_callable($details['where']), function ($query) use ($details) {
                    return $details['where']($query);
                })
                ->when(filled($details['searchable']) and filled($q) and is_array($details['searchable']), function ($query) use ($q, $details) {
                    foreach ($details['searchable'] as $field) {
                        $query->orWhereRaw("LOWER({$field}) LIKE ?", ['%' . strtolower($q) . '%']);
                    }
                })
                ->when(isset($details['order_by']) && is_array($details['order_by']), function ($query) use ($details) {
                    foreach ($details['order_by'] as $field => $direction) {
                        $query->orderBy($field, $direction);
                    }
                }, function ($query) use ($details) {
                    $query->orderBy($details['text'], 'asc');
                })
                ->limit(config('select2-ajax.result_limit', 10))
                ->get();
            };

            if ($cacheTtl > 0) {
                $cacheKey = 'select2:' . md5(json_encode([
                    'models' => $models,
                    'details' => $details,
                    'q' => $q,
                ]));
                $data = Cache::remember($cacheKey, $cacheTtl, $queryBuilder);
            } else {
                $data = $queryBuilder();
            }

            return response()->json(['data' => $data]);
        } catch (\Throwable $th) {
            Log::error('Select2Controller search error', [
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString(),
            ]);
            return response()->json(['error' => 'An error occurred while processing your request.'], 500);
        }
    }
}