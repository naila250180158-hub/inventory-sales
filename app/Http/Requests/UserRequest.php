<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->route('user'); // null saat tambah

        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            // saat tambah password wajib; saat ubah boleh kosong (tidak diganti)
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'max:100'],
            'role' => ['required', 'in:admin,staff'],
        ];
    }
}
