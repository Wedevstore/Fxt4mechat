<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;

class SubscriptionMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if ($user && $user->client) {
            $client = $user->client;
            if ($client->activeSubscription) {
                return $next($request);
            }
            if ($client->pendingSubscription) {
                return redirect()->route('client.pending.subscription');
            }
            Toastr::warning(__('subscribe_plan_to_access'));

            return redirect()->route('client.available.plans');
        }
        return redirect()->route('login');
    }
}