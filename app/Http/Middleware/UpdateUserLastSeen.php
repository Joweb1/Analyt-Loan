<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateUserLastSeen
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user) {
            $lastSeen = $user->last_seen_at;
            $needsUpdate = false;

            if (! $lastSeen) {
                $needsUpdate = true;
            } else {
                if ($lastSeen->diffInMinutes(now()) >= 1) {
                    $needsUpdate = true;
                }
            }

            if ($needsUpdate) {
                \Illuminate\Support\Facades\DB::table('users')
                    ->where('id', $user->id)
                    ->update(['last_seen_at' => now()]);
            }
        }

        return $next($request);
    }
}
