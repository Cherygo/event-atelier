<?php

namespace App\Http\Requests;

use App\TaskTemplateCatalog;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ImportTaskTemplateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        Gate::authorize('view', $this->route('event'));

        return $this->user()->can('editPlanning', $this->route('event'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(TaskTemplateCatalog $catalog): array
    {
        $key = $this->input('template');
        $template = is_string($key) ? $catalog->find($key) : null;

        return [
            'template' => ['required', 'string', Rule::in(array_column($catalog->all(), 'key'))],
            'items' => ['required', 'array', 'min:1', 'max:30'],
            'items.*' => ['required', 'string', 'distinct:strict', Rule::in(array_column($template['items'] ?? [], 'key'))],
        ];
    }

    public function messages(): array
    {
        return ['items.required' => 'Choose at least one task to add.', 'items.*.in' => 'Choose tasks from the selected template.', 'items.*.distinct' => 'Each task can only be selected once.'];
    }
}
