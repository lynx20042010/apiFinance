<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompteRequest extends FormRequest
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
            'client_id' => 'required|uuid|exists:clients,id',
            'numeroCompte' => 'sometimes|string|unique:comptes,numeroCompte|regex:/^CPT\d{10}$/',
            'type' => 'required|in:courant,epargne,titre,devise',
            'devise' => 'required|string|size:3|in:XAF,EUR,USD,CAD,GBP',
            'statut' => 'sometimes|in:actif,inactif,bloque,ferme',
            'solde' => 'sometimes|numeric|min:0',
            'metadata' => 'sometimes|array',
            'metadata.date_ouverture' => 'sometimes|date|before_or_equal:today',
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
            'client_id.required' => 'Le client est obligatoire.',
            'client_id.uuid' => 'L\'ID client doit être un UUID valide.',
            'client_id.exists' => 'Le client spécifié n\'existe pas.',
            'numeroCompte.unique' => 'Ce numéro de compte est déjà utilisé.',
            'numeroCompte.regex' => 'Le format du numéro de compte est invalide.',
            'type.required' => 'Le type de compte est obligatoire.',
            'type.in' => 'Le type doit être courant, épargne, titre ou devise.',
            'devise.required' => 'La devise est obligatoire.',
            'devise.size' => 'La devise doit contenir exactement 3 caractères.',
            'devise.in' => 'La devise doit être XAF, EUR, USD, CAD ou GBP.',
            'statut.in' => 'Le statut doit être actif, inactif, bloqué ou fermé.',
            'solde.numeric' => 'Le solde doit être un nombre.',
            'solde.min' => 'Le solde ne peut pas être négatif.',
            'metadata.array' => 'Les métadonnées doivent être un tableau.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (!$this->has('numeroCompte') || empty($this->numeroCompte)) {
            $this->merge([
                'numeroCompte' => \App\Models\Compte::generateNumeroCompte()
            ]);
        }

        if (!$this->has('statut')) {
            $this->merge([
                'statut' => 'actif'
            ]);
        }

        if (!$this->has('solde')) {
            $this->merge([
                'solde' => 0
            ]);
        }
    }
}
