<?php

namespace App\Providers;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        $this->configureDefaults();
        $this->configureTimeTravel();
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null
        );
    }

    /**
     * Configure time travel for testing exam times
     * Set APP_TEST_TIME in .env to a specific date/time to "travel" to that time
     * Format: Y-m-d H:i:s (e.g., 2026-01-15 12:00:00)
     * Set to "now" or leave empty to use real time
     */
    protected function configureTimeTravel(): void
    {
        // Only enable in non-production environments
        if (app()->isProduction()) {
            return;
        }

        $testTime = env('APP_TEST_TIME');

        if ($testTime && $testTime !== 'now' && $testTime !== '') {
            try {
                // Parse the time
                $time = Carbon::parse($testTime, config('app.timezone'));
                
                // Set test time for both Carbon and CarbonImmutable
                Carbon::setTestNow($time);
                CarbonImmutable::setTestNow($time->toImmutable());
                
                // Also set for Date facade (which uses CarbonImmutable)
                Date::setTestNow($time->toImmutable());
                
                // Log for debugging
                if (config('app.debug')) {
                    \Log::info("Time travel activated: Current test time is {$time->format('Y-m-d H:i:s T')}");
                }
            } catch (\Exception $e) {
                \Log::warning("Invalid APP_TEST_TIME format: {$testTime}. Using real time. Error: {$e->getMessage()}");
            }
        } else {
            // Clear test time if set to 'now' or empty
            Carbon::setTestNow(null);
            CarbonImmutable::setTestNow(null);
            Date::setTestNow(null);
        }
    }
}
