<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Menangani bahasa aplikasi berdasarkan session atau query parameter 'lang'.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Dukung pergantian langsung lewat query parameter misal ?lang=en atau ?lang=id
        if ($request->has('lang') && in_array($request->query('lang'), ['id', 'en'], true)) {
            $locale = $request->query('lang');
            Session::put('locale', $locale);
        }

        // 2. Baca dari session, fallback ke default config aplikasi ('id')
        $locale = Session::get('locale', config('app.locale', 'id'));

        if (!in_array($locale, ['id', 'en'], true)) {
            $locale = 'id';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
