<?php

namespace TeguhRijanandi\LaravelSelect2Ajax\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;
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

            // Get all data from the model (collection_builder already handles caching)
            $raw_data = $this->collection_builder($models, $details, $cacheTtl);

            // If the query is empty, return all data with limit
            if (empty($q)) {
                $data = $raw_data->map(function ($item) use ($details) {
                    return [
                        'id' => $item[$details['id']],
                        'text' => $item[$details['text']],
                    ];
                })
                ->values()
                ->take(config('select2-ajax.result_limit', 10))
                ->all();

                return response()->json(['data' => $data]);
            }

            // Filter and map the data based on the query and searchable fields
            $filteredData = $raw_data->filter(function ($item) use ($q, $details) {
                foreach ($details['searchable'] as $field) {
                    if (isset($item[$field]) && stripos($item[$field], $q) !== false) {
                        return true;
                    }
                }
                return false;
            })
            ->map(function ($item) use ($details) {
                return [
                    'id' => $item[$details['id']],
                    'text' => $item[$details['text']],
                ];
            })
            ->values()
            ->take(config('select2-ajax.result_limit', 10))
            ->all();

            // Return the data as a JSON response
            return response()->json(['data' => $filteredData]);
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

    /**
     * Build a collection from the model based on the provided details.
     *
     * @param string $models
     * @param array $details
     * @return Collection
     */
    protected function collection_builder(string $models, array $details, int $cacheTtl = 0): Collection
    {
        try {
            $cacheKey = 'select2:collection:' . md5(json_encode([$models, $details]));
            $select = "{$details['id']} as id, {$details['text']} as text";
            if (!empty($details['searchable'])) {
                $select .= ', ' . implode(',', $details['searchable']);
            }

            $query = fn() => app($models)::query()
                                        ->selectRaw($select)
                                        ->when(isset($details['order_by']) && is_array($details['order_by']), function ($query) use ($details) {
                                            foreach ($details['order_by'] as $field => $direction) {
                                                $query->orderBy($field, $direction);
                                            }
                                        }, function ($query) use ($details) {
                                            $query->orderBy($details['text'], 'asc');
                                        })
                                        ->get();

            return $cacheTtl > 0
                ? Cache::remember($cacheKey, $cacheTtl, $query)
                : $query();
        } catch (\Throwable $th) {
            Log::error('Select2Controller collection_builder error', [
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString(),
            ]);
            return new Collection();
        }
    }
}