<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use App\Models\StudentReference;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $validated = Validator::make($input, [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'middle_initial' => ['nullable', 'string', 'max:10'],
            'name_extension' => ['nullable', 'string', 'max:20'],
            'email' => $this->emailRules(),
            'id_number' => ['required', 'string', 'max:255', Rule::unique(User::class, 'id_number')],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'password' => $this->passwordRules(),
        ])->validate();

        $reference = StudentReference::query()->where('id_number', trim($validated['id_number']))->first();
        if (StudentReference::query()->exists() && (
            $reference === null
            || $this->normalizeName($reference->name) !== $this->normalizeName($this->registrationName($validated))
            || $reference->gender !== $validated['gender']
        )) {
            throw ValidationException::withMessages([
                'id_number' => 'Plese your valid credentials',
            ]);
        }

        return User::create([
            'name' => implode(' ', array_filter([
                trim($validated['first_name']),
                filled($validated['middle_initial'] ?? null) ? trim($validated['middle_initial']).'.' : null,
                trim($validated['last_name']),
                filled($validated['name_extension'] ?? null) ? trim($validated['name_extension']) : null,
            ])),
            'id_number' => $validated['id_number'],
            'gender' => $validated['gender'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);
    }

    /** @param array<string, string|null> $input */
    private function registrationName(array $input): string
    {
        return implode(' ', array_filter([
            trim($input['first_name']),
            filled($input['middle_initial'] ?? null) ? trim($input['middle_initial']).'.' : null,
            trim($input['last_name']),
            filled($input['name_extension'] ?? null) ? trim($input['name_extension']) : null,
        ]));
    }

    private function normalizeName(string $name): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower(trim($name))) ?? '';
    }
}
