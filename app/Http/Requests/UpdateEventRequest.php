<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Gate;

class UpdateEventRequest extends StoreEventRequest
{
    public function authorize(): bool
    {
        Gate::authorize('view', $this->route('event'));

        return $this->user()->can('update', $this->route('event'));
    }
}
