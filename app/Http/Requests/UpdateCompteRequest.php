<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // À adapter selon les besoins d'autorisation
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => 'sometimes|in:cheque,courant,epargne,titre,devise',
            'devise' => 'sometimes|string|size:3|in:XAF,EUR,USD,CAD,GBP',
            'statut' => 'sometimes|in:actif,inactif,bloque,ferme',
            'metadata' => 'sometimes|array',
            'metadata.agence' => 'sometimes|string|max:100',
            'metadata.rib' => 'sometimes|string|max:50',
            'metadata.iban' => 'sometimes|string|max:50'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.in' => 'Le type doit être cheque, courant, épargne, titre ou devise.',
            'devise.size' => 'La devise doit contenir exactement 3 caractères.',
            'devise.in' => 'La devise doit être XAF, EUR, USD, CAD ou GBP.',
            'statut.in' => 'Le statut doit être actif, inactif, bloqué ou fermé.',
            'metadata.array' => 'Les métadonnées doivent être un tableau.',
            'metadata.agence.max' => 'Le nom de l\'agence ne peut pas dépasser 100 caractères.',
            'metadata.rib.max' => 'Le RIB ne peut pas dépasser 50 caractères.',
            'metadata.iban.max' => 'L\'IBAN ne peut pas dépasser 50 caractères.',
        ];
    }
}
