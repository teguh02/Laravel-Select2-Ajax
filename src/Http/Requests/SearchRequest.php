<?php

namespace TeguhRijanandi\LaravelSelect2Ajax\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     * @see https://select2.org/data-sources/ajax#request-parameters
     *
     * @return array
     */
    public function rules()
    {
        return [
            'q' => 'nullable|string|min:1|max:50',
            'query' => 'required|string|min:1|max:50',
        ];
    }
}