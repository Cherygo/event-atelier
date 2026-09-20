<?php

namespace App\Http\Requests;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class SaveEventTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        Gate::authorize('view', $this->route('event'));

        return $this->user()->can('editPlanning', $this->route('event'));
    }

    public function forEvent(Event $event): array
    {
        $data = $this->validated();
        if (! empty($data['assigned_to']) && ! $event->assignableUsers()->whereKey($data['assigned_to'])->exists()) {
            throw ValidationException::withMessages(['assigned_to' => 'Choose an owner, admin, or editor with access to this event.']);
        }

        return $data;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'category' => ['nullable', 'string', 'max:60'],
            'due_date' => ['nullable', 'date_format:Y-m-d'],
            'assigned_to' => ['nullable', 'integer'],
        ];
    }
}
