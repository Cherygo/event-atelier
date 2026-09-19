<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateEventMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        Gate::authorize('view', $this->route('event'));

        return $this->user()->can('manageMember', [$this->route('event'), $this->route('member')]);
    }

    public function rules(): array
    {
        return ['role' => ['required', 'in:admin,editor,viewer']];
    }
}
