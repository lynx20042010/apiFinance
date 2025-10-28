<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
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
            'user_id' => 'required|uuid|exists:users,id',
            'numeroCompte' => 'sometimes|string|unique:clients,numeroCompte|regex:/^CLT\d{10}$/',
            'titulaire' => 'required|string|max:255',
            'type' => 'required|in:particulier,entreprise',
            'devise' => 'required|string|size:3|in:XAF,EUR,USD,CAD,GBP',
            'statut' => 'sometimes|in:actif,inactif,suspendu',
            'metadata' => 'sometimes|array',
            'metadata.telephone' => 'sometimes|string|max:20',
            'metadata.adresse' => 'sometimes|string|max:500',
            'metadata.date_naissance' => 'sometimes|date|before:today',
            'metadata.profession' => 'sometimes|string|max:100',
            'metadata.secteur' => 'sometimes|string|max:100',
            'metadata.numero_rc' => 'sometimes|string|max:50'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'L\'utilisateur est obligatoire.',
            'user_id.uuid' => 'L\'ID utilisateur doit être un UUID valide.',
            'user_id.exists' => 'L\'utilisateur spécifié n\'existe pas.',
            'numeroCompte.unique' => 'Ce numéro de compte est déjà utilisé.',
            'numeroCompte.regex' => 'Le format du numéro de compte est invalide.',
            'titulaire.required' => 'Le nom du titulaire est obligatoire.',
            'type.required' => 'Le type de client est obligatoire.',
            'type.in' => 'Le type doit être particulier ou entreprise.',
            'devise.required' => 'La devise est obligatoire.',
            'devise.size' => 'La devise doit contenir exactement 3 caractères.',
            'devise.in' => 'La devise doit être XAF, EUR, USD, CAD ou GBP.',
            'statut.in' => 'Le statut doit être actif, inactif ou suspendu.',
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
                'numeroCompte' => \App\Models\Client::generateNumeroCompte()
            ]);
        }

        if (!$this->has('statut')) {
            $this->merge([
                'statut' => 'actif'
            ]);
        }
    }
}
