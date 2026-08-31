<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $userRole = strtolower(Auth::user()->role ?? '');

        // Super Admin bebas akses halaman manapun (Admin & CS Console)
        if ($userRole === 'admin') {
            return $next($request);
        }

        // Jika CS mencoba masuk ke halaman Admin, kembalikan ke CS Desk
        if ($userRole === 'cs' && strtolower($role) === 'admin') {
            return redirect()->route('cs.index')->with('error', 'Akses ditolak! Halaman ini khusus Super Admin.');
        }

        return $next($request);
    }
}