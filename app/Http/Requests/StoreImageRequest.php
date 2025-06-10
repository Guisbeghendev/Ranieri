<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use App\Models\Gallery; // Importe o modelo Gallery

class StoreImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // A autorização deve verificar se o usuário pode gerenciar a galeria.
        // O Laravel injetará a `Gallery` automaticamente se a rota estiver configurada com route model binding.
        $gallery = $this->route('gallery'); // Pega a instância da galeria da rota
        return Gate::allows('manage-gallery', $gallery);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'image', 'max:20480'], // Valida o arquivo de imagem, 20MB max
        ];
    }
}
