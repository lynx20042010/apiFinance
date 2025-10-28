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
            'type' => 'required|in:cheque,courant,epargne,titre,devise',
            'soldeInitial' => 'required|numeric|min:10000',
            'devise' => 'required|string|size:3|in:XAF,EUR,USD,CAD,GBP',
            'client' => 'required|array',
            'client.id' => 'nullable|string',
            'client.titulaire' => 'required_without:client.id|string|max:255',
            'client.email' => 'required_without:client.id|email|unique:users,email',
            'client.telephone' => 'required_without:client.id|string|regex:/^\+221[0-9]{9}$/',
            'client.adresse' => 'required_without:client.id|string|max:500'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Le type de compte est obligatoire.',
            'type.in' => 'Le type doit être cheque, courant, épargne, titre ou devise.',
            'soldeInitial.required' => 'Le solde initial est obligatoire.',
            'soldeInitial.numeric' => 'Le solde initial doit être un nombre.',
            'soldeInitial.min' => 'Le solde initial doit être d\'au moins 10 000.',
            'devise.required' => 'La devise est obligatoire.',
            'devise.size' => 'La devise doit contenir exactement 3 caractères.',
            'devise.in' => 'La devise doit être XAF, EUR, USD, CAD ou GBP.',
            'client.required' => 'Les informations du client sont obligatoires.',
            'client.array' => 'Les informations du client doivent être un tableau.',
            'client.id.uuid' => 'L\'ID client doit être un UUID valide.',
            'client.titulaire.required_without' => 'Le nom du titulaire est obligatoire.',
            'client.titulaire.string' => 'Le nom du titulaire doit être une chaîne de caractères.',
            'client.titulaire.max' => 'Le nom du titulaire ne peut pas dépasser 255 caractères.',
            'client.email.required_without' => 'L\'email est obligatoire.',
            'client.email.email' => 'L\'email doit être valide.',
            'client.email.unique' => 'Cet email est déjà utilisé.',
            'client.telephone.required_without' => 'Le téléphone est obligatoire.',
            'client.telephone.string' => 'Le téléphone doit être une chaîne de caractères.',
            'client.telephone.regex' => 'Le format du téléphone est invalide (+221XXXXXXXXX).',
            'client.adresse.required_without' => 'L\'adresse est obligatoire.',
            'client.adresse.string' => 'L\'adresse doit être une chaîne de caractères.',
            'client.adresse.max' => 'L\'adresse ne peut pas dépasser 500 caractères.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Préparation automatique des données si nécessaire
        // Les valeurs par défaut sont gérées dans les modèles et observers
    }
}
