<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    /**
     * Indica si el usuario autenticado puede crear la publicación.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Expone las reglas de validación para almacenar una publicación.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:5000'],
            'group_id' => ['nullable', 'exists:groups,id'],
            'game_id' => ['nullable', 'exists:games,id'],
            'rawg_game_id' => ['nullable', 'integer'],
            'game_title' => ['nullable', 'string', 'max:255'],
            'game_image' => ['nullable', 'string', 'max:500'],
            'game_platform' => ['nullable', 'string', 'max:255'],
            'visibility' => ['nullable', 'in:public,private,group'],
            'images' => ['nullable', 'array', 'max:4'],
            'images.*' => ['image', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'],
        ];
    }

    /**
     * Configura los mensajes personalizados de validación.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'content.required' => 'El contenido de la publicación es obligatorio',
            'content.max' => 'El contenido no puede exceder los 5000 caracteres',
            'group_id.exists' => 'El grupo seleccionado no existe',
            'game_id.exists' => 'El juego seleccionado no existe',
            'visibility.in' => 'La visibilidad debe ser: pública, privada o de grupo',
            'images.array' => 'Las imágenes deben enviarse en un formato válido.',
            'images.max' => 'Solo puedes adjuntar hasta 4 imágenes.',
            'images.*.image' => 'Cada archivo debe ser una imagen válida.',
            'images.*.mimes' => 'Las imágenes deben ser jpeg, jpg, png, gif o webp.',
            'images.*.max' => 'Cada imagen no puede ser mayor de 5MB.',
        ];
    }
}
