<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Paksa timezone PHP ke Asia/Jakarta
        date_default_timezone_set('Asia/Jakarta');

        // Paksa setiap koneksi query database MySQL ke timezone WIB (+07:00)
        try {
            DB::statement("SET time_zone='+07:00';");
        } catch (\Exception $e) {
            // Abaikan jika database belum siap saat proses build
        }
    }
}