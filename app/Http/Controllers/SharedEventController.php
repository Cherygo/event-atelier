<?php

namespace App\Http\Controllers;

use App\Models\EventShare;
use App\SharedEventData;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class SharedEventController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, string $token, SharedEventData $data): Response
    {
        $share = preg_match('/\A[a-zA-Z0-9]{64}\z/', $token)
            ? EventShare::query()->with('event')->where('token_hash', hash('sha256', $token))->first()
            : null;

        return Inertia::render('Shared/Show', [
            'plan' => $share ? $data->forEvent($share->event, $share) : null,
        ])->toResponse($request)->setStatusCode($share ? 200 : 404);
    }
}
