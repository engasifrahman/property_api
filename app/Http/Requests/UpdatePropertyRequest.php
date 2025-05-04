<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePropertyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'bill_country_code' => 'sometimes|required|string|size:3',
            'description' => 'nullable|string',
            'address_line_1' => 'sometimes|required|string|max:255',
            'address_line_2' => 'sometimes|required|string|max:255',
            'address_line_3' => 'sometimes|required|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'google_place_id' => 'nullable|string|max:255',
            'city' => 'sometimes|required|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'sometimes|required|string|max:255',
            'zip_code' => 'nullable|string|max:20',
            'star_rating' => 'nullable|integer|min:0|max:5',
            'property_type' => 'sometimes|required|string|in:hotel,resort,guesthouse,bnb',
            'is_active' => 'sometimes|boolean',
            'is_deleted' => 'sometimes|boolean',
        ];
    }
}
