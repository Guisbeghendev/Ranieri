<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Models\Gallery; // Importe o modelo Gallery

class StoreGalleryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Certifique-se de que o usuário está autorizado a criar uma galeria.
        // Isso usa a sua Gate 'create' definida para o modelo Gallery.
        return Gate::allows('create', Gallery::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'event_date' => ['nullable', 'date'],
            'selected_group_ids' => ['required', 'array'],
            'selected_group_ids.*' => ['integer', 'exists:groups,id'],
            'selected_watermark' => ['nullable', 'string', 'max:255'],
        ];
    }
}
