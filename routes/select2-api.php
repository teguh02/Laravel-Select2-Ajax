<?php use Illuminate\Support\Facades\Route;

use TeguhRijanandi\LaravelSelect2Ajax\Http\Controllers\Select2Controller;

Route::group([
    'prefix' => 'api/' . trim(config('select2-ajax.search_url'), '/'),
    'middleware' => config('select2-ajax.middleware', ['api']),
], function () {
    Route::match(['GET', 'POST'], '/', [Select2Controller::class, 'search'])
        ->name(trim(config('select2-ajax.search_route_name')));
});