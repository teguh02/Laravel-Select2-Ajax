<?php

use App\Models\User;

return [

    /*
    |--------------------------------------------------------------------------
    | Search2 API URL
    |--------------------------------------------------------------------------
    |
    | This value is the URL for the Select2 search api endpoint, which will be used
    | when the framework needs to perform a search using the Select2 component.
    | This will be return /api/select2/search by default.
    |
    */
    'search_url' => env('SELECT2_SEARCH_URL', '/select2/search'),

    /*
    |--------------------------------------------------------------------------
    | Search2 API Route Name
    |--------------------------------------------------------------------------
    |
    | This value is the route name for the Select2 search api endpoint, which will be used
    | when the framework needs to perform a search using the Select2 component.
    |
    */
    'search_route_name' => env('SELECT2_SEARCH_ROUTE_NAME', 'select2.search'),

    /*
    |--------------------------------------------------------------------------
    | Result Limit
    |--------------------------------------------------------------------------
    |
    | This value is the limit of results that will be returned by the Select2 search api.
    | This will be used to limit the number of results returned by the search query.
    | You can set this value in your .env file.
    |
    */
    'result_limit' => env('SELECT2_RESULT_LIMIT', 10),

    /*
    |--------------------------------------------------------------------------
    | Query Configuration
    |--------------------------------------------------------------------------
    |
    | This value is the configuration for the Select2 search query. You can define
    | the model, the id, the text, the searchable fields, the order by fields,
    | and the where clause for the query. This will be used to perform the search
    | using the Select2 component.
    |
    | Example:
    | 'query' => [
    |     User::class => [
    |         'id' => 'id',
    |         'text' => 'name', 
    |         'searchable' => ['name', 'email'],
    |         'order_by' => ['name' => 'asc'],
    |         'where' => null,
    |         // 'where' => function ($query) {
    |         //     return $query->whereNotNull('email_verified_at');
    |         // },
    |     ],
    |
    | id: The field that will be used as the id in the response.
    | text: The field that will be used as the text in the response.
    | searchable: The fields that will be used for searching.
    | order_by: The fields that will be used for ordering the results.
    | where: The where clause for the query.
    */

    'query' => [
        User::class => [
            'id' => 'id',
            'text' => 'name',
            'searchable' => ['name', 'email'],
            'order_by' => ['name' => 'asc'],
            'where' => null,
            // 'where' => function ($query) {
            //     return $query->whereNotNull('email_verified_at');
            // },
        ],

        // Add more models as needed
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | This value is the configuration for caching the results of the Select2 search.
    | You can set the cache ttl (time to live) in minutes. Set to 0 to disable caching.
    */  

    'cache_ttl' => 60, // Cache TTL in minutes

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | This value is the middleware that will be applied to the Select2 search api route.
    | You can define the middleware that will be applied to the route.
    | By default, it will use the 'api' middleware group.
    |
    */

    'middleware' => [
        'api'
    ],
];
