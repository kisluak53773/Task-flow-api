<?php

declare(strict_types=1);

namespace Presentation\Api\Request\Project;

use Domain\Project\ValueObject\ProjectRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class InviteUserRequest extends FormRequest
{
    public  function authorize(): bool
    {
        return true;
    }

    public  function rules(): array
    {
        return  [
            'email' => 'required|email',
            'role' => ['required', new Enum(ProjectRole::class)]
        ];
    }
}
