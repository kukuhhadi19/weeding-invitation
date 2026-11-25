<?php

namespace App\Http\Middleware;

use App\Services\SubscriptionLimitService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWithinInvitationQuota
{
    public function __construct(protected SubscriptionLimitService $limitService) {}

    /**
     * Block invitation creation when the authenticated user has reached their tier quota.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Unauthorized.');
        }

        if ($this->limitService->hasReachedInvitationLimit($user)) {
            $message = 'Kuota undangan aktif Anda sudah penuh. Silakan upgrade paket langganan untuk membuat lebih banyak undangan.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 403);
            }

            return redirect()
                ->route('subscription.index')
                ->with('error', $message);
        }

        return $next($request);
    }
}
