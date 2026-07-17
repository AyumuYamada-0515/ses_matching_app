<?php

namespace App\Http\Requests;

use App\Enums\ProjectStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSales() ?? false;
    }

    public function rules(): array
    {
        return ['title' => ['required', 'string', 'max:255'], 'description' => ['required', 'string'], 'required_skills' => ['required', 'string'], 'preferred_skills' => ['nullable', 'string'], 'process' => ['required', 'string', 'max:255'], 'location' => ['required', 'string', 'max:255'], 'remote_type' => ['required', Rule::in(['office', 'hybrid', 'remote'])], 'min_price' => ['required', 'integer', 'min:0'], 'max_price' => ['nullable', 'integer', 'gte:min_price'], 'recruitment_count' => ['required', 'integer', 'min:1'], 'start_date' => ['nullable', 'date'], 'application_deadline' => ['required', 'date', 'after_or_equal:today'], 'status' => ['required', Rule::enum(ProjectStatus::class)]];
    }
}