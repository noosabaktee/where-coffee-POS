<?php

namespace App\Http\Middleware;

use App\Models\Outlet;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveCurrentOutlet
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless($user, 401);

        if ($user->hasRole('Administrator')) {
            $requestedId = (int) $request->session()->get('current_outlet_id');
            $outlet = Outlet::query()
                ->where('is_active', true)
                ->when($requestedId > 0, fn ($query) => $query->whereKey($requestedId))
                ->first();

            $outlet ??= Outlet::query()->where('is_active', true)->orderBy('id')->first();
        } else {
            $outlet = $user->outlet()->where('is_active', true)->first();
        }

        abort_unless($outlet, 422, 'Tidak ada outlet aktif yang dapat digunakan.');

        $request->session()->put('current_outlet_id', $outlet->getKey());
        $request->attributes->set('current_outlet', $outlet);

        return $next($request);
    }
}
