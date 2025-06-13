# Laravel Select2 Ajax API Backend

A simple, flexible, and reusable backend API for [Select2](https://select2.org/) AJAX dropdowns in Laravel.

## Features

- Dynamic model and field configuration via config file
- Supports search, filtering, and custom where clauses
- Caching support for improved performance
- Pagination/limit support
- Easy integration with Select2 frontend

## Installation

Install the package via Composer:

```bash
composer require teguh02/laravel-select2-ajax
```

Publish the configuration file:

```bash
php artisan vendor:publish --provider="TeguhRijanandi\LaravelSelect2Ajax\LaravelSelect2AjaxServiceProvider"
```

1. **Register the service provider and routes as needed.**
2. **Publish the configuration file (if available).**

## Configuration

Configure your searchable models and fields in `config/select2-ajax.php`:

```php
return [
    'query' => [
        // Example:
        \App\Models\User::class => [
            'id' => 'id', // The field that will be used as the id in the response.
            'text' => 'name', // The field that will be used as the text in the response.
            'searchable' => ['name', 'email'], // The fields that will be used for searching.
            // Optional:
            // 'order_by' => 'name', // The field that will be used for ordering the results.
            // 'where' => fn($query) => $query->where('active', 1), // The where clause for the query.
        ],
        // Add more models as needed...
    ],
    'result_limit' => 10,
    'cache_ttl' => 60, // seconds, 0 = no cache
];
```

**Configuration Notes:**
- `id`: The field that will be used as the id in the response.
- `text`: The field that will be used as the text in the response.
- `searchable`: The fields that will be used for searching.
- `order_by`: The field that will be used for ordering the results.
- `where`: The where clause for the query (closure).

## Usage

### API Endpoint

Send a GET or POST request to the endpoint (e.g. `/api/select2/search`) with the following parameters:

- `q` (string): The search term entered by the user.
- `query` (string): The model key as configured (e.g. `User`).

**Example Request:**

```http
GET /api/select2/search?q=john&query=User
```

**Example Response:**

```json
{
  "data": [
    { "id": 1, "text": "John Doe" },
    { "id": 2, "text": "Johnny Appleseed" }
  ]
}
```

### Frontend Integration

Configure your Select2 input to use AJAX:

```javascript
$('#your-select').select2({
    ajax: {
        url: '/api/select2/search',
        dataType: 'json',
        delay: 250,
        data: function (params) {
            return {
                q: params.term,
                query: 'User' // or your configured model key
            };
        },
        processResults: function (data) {
            return {
                results: data.data
            };
        }
    }
});
```

## Advanced

- **Custom Filtering:**  
  Use the `where` closure in config for custom query logic.
- **Caching:**  
  Set `cache_ttl` in config to cache results for faster repeated queries.
- **Result Limit:**  
  Adjust `result_limit` in config to control how many results are returned.

## HTTP Codes

- **200**: Success, data returned as expected.
- **400**: The query type is invalid.
- **404**: The model configuration is missing.
- **500**: Unexpected errors (see Laravel logs for details).

## License

MIT or your preferred license.

## Contributing

Contributions are welcome! To contribute to this library:

1. **Fork the repository** and create your branch from `main`.
2. **Make your changes** with clear commit messages.
3. **Test your changes** to ensure nothing is broken.
4. **Submit a pull request** describing your changes and why they should be merged.

If you find a bug or have a feature request, please open an issue.