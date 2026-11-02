<?php

namespace App\Http\Requests\Student;

use App\Models\Assignment;
use Illuminate\Foundation\Http\FormRequest;

class SubmitAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $assignment = $this->route('assignment');

        return $assignment instanceof Assignment
            && $this->user()->can('submit', $assignment);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'answer' => ['required', 'string', 'max:10000'],
        ];
    }
}
