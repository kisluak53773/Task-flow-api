<?php

declare(strict_types=1);

namespace Presentation\Api\Request;

use Illuminate\Foundation\Http\FormRequest;

class RegsiterUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:8|max:255|confirmed',
        ];
    }
}
