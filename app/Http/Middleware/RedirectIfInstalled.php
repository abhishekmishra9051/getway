<?php

namespace App\Http\Middleware;

use App\Support\InstallationManager;
use Closure;
use Illuminate\Http\Request;

class RedirectIfInstalled
{
    public function __construct(private InstallationManager $installer) {}

    public function handle(Request $request, Closure $next)
    {
        if ($this->installer->isInstalled()
            && ! ($request->routeIs('install.done') && $request->session()->has('installer.completed'))) {
            return redirect()->route('home');
        }

        return $next($request);
    }
}
