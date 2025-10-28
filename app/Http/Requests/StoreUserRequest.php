<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
        $userId = $this->route('user')?->id ?? null;

        return [
            'name' => 'required|string|max:255|min:2',
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
                $userId ? 'unique:users,email,' . $userId : 'unique:users,email'
            ],
            'password' => $userId ? 'sometimes|string|min:8|confirmed' : 'required|string|min:8|confirmed',
            'password_confirmation' => $userId ? 'sometimes|required_with:password|same:password' : 'required|same:password',
            'email_verified_at' => 'sometimes|nullable|date|before:now',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom est obligatoire.',
            'name.string' => 'Le nom doit être une chaîne de caractères.',
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'name.min' => 'Le nom doit contenir au moins 2 caractères.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.string' => 'L\'adresse email doit être une chaîne de caractères.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.max' => 'L\'adresse email ne peut pas dépasser 255 caractères.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.string' => 'Le mot de passe doit être une chaîne de caractères.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password_confirmation.required_with' => 'La confirmation du mot de passe est requise.',
            'password_confirmation.same' => 'La confirmation du mot de passe doit correspondre.',
            'email_verified_at.date' => 'La date de vérification email doit être une date valide.',
            'email_verified_at.before' => 'La date de vérification email ne peut pas être dans le futur.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Hash le mot de passe si présent
        if ($this->has('password') && !empty($this->password)) {
            $this->merge([
                'password' => bcrypt($this->password)
            ]);
        }

        // Convertir la date de vérification si présente
        if ($this->has('email_verified_at') && !empty($this->email_verified_at)) {
            $this->merge([
                'email_verified_at' => $this->email_verified_at === 'now' ? now() : $this->email_verified_at
            ]);
        }
    }

    /**
     * Get the validated data from the request.
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        // Ne pas retourner le password_confirmation
        unset($validated['password_confirmation']);

        return $validated;
    }
}
