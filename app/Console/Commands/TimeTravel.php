<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;

class TimeTravel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'time:travel 
                            {time? : The time to travel to (format: Y-m-d H:i:s or relative like "+2 hours", "tomorrow", etc.)}
                            {--clear : Clear time travel and use real time}
                            {--show : Show current test time}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Time travel for testing exam times. Set a fake time or clear it.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Prevent in production
        if (app()->isProduction()) {
            $this->error('Time travel is disabled in production environment.');
            return Command::FAILURE;
        }

        // Show current test time
        if ($this->option('show')) {
            $testTime = Carbon::getTestNow() ?? \Carbon\CarbonImmutable::getTestNow();
            if ($testTime) {
                $this->info("Current test time: {$testTime->format('Y-m-d H:i:s T')}");
                // Temporarily clear to get real time
                $originalTest = Carbon::getTestNow();
                Carbon::setTestNow(null);
                \Carbon\CarbonImmutable::setTestNow(null);
                $this->info("Real time: " . Carbon::now()->format('Y-m-d H:i:s T'));
                // Restore test time
                if ($originalTest) {
                    Carbon::setTestNow($originalTest);
                    \Carbon\CarbonImmutable::setTestNow($originalTest->toImmutable());
                }
            } else {
                $this->info("Time travel is not active. Using real time.");
                $this->info("Current time: " . Carbon::now()->format('Y-m-d H:i:s T'));
            }
            return Command::SUCCESS;
        }

        // Clear time travel
        if ($this->option('clear')) {
            Carbon::setTestNow(null);
            \Carbon\CarbonImmutable::setTestNow(null);
            Date::setTestNow(null);
            $this->info('Time travel cleared. Using real time now.');
            $this->warn('Note: You need to restart your application (php artisan serve) for this to take effect.');
            return Command::SUCCESS;
        }

        // Set time travel
        $timeInput = $this->argument('time');
        
        if (!$timeInput) {
            $this->error('Please provide a time or use --clear to reset.');
            $this->info('');
            $this->info('Examples:');
            $this->info('  php artisan time:travel "2026-01-15 12:00:00"');
            $this->info('  php artisan time:travel "+2 hours"');
            $this->info('  php artisan time:travel "tomorrow 10:00"');
            $this->info('  php artisan time:travel --clear');
            $this->info('  php artisan time:travel --show');
            return Command::FAILURE;
        }

        try {
            // Try to parse as relative time first (e.g., "+2 hours", "tomorrow")
            if (str_starts_with($timeInput, '+') || str_starts_with($timeInput, '-') || 
                in_array(strtolower($timeInput), ['now', 'today', 'tomorrow', 'yesterday'])) {
                $time = Carbon::parse($timeInput, config('app.timezone'));
            } else {
                // Try to parse as absolute datetime
                $time = Carbon::createFromFormat('Y-m-d H:i:s', $timeInput, config('app.timezone'));
            }

            // Set for both Carbon and CarbonImmutable
            Carbon::setTestNow($time);
            \Carbon\CarbonImmutable::setTestNow($time->toImmutable());
            Date::setTestNow($time->toImmutable());

            $this->info("✓ Time travel activated!");
            $this->info("  Test time: {$time->format('Y-m-d H:i:s T')}");
            // Temporarily clear to show real time
            $originalTest = Carbon::getTestNow();
            Carbon::setTestNow(null);
            \Carbon\CarbonImmutable::setTestNow(null);
            $realTime = Carbon::now();
            $this->info("  Real time: " . $realTime->format('Y-m-d H:i:s T'));
            // Restore test time
            if ($originalTest) {
                Carbon::setTestNow($originalTest);
                \Carbon\CarbonImmutable::setTestNow($originalTest->toImmutable());
            } else {
                Carbon::setTestNow($time);
                \Carbon\CarbonImmutable::setTestNow($time->toImmutable());
            }
            $this->warn('');
            $this->warn('⚠ Note: You need to restart your application (php artisan serve) for this to take effect.');
            $this->warn('   Or set APP_TEST_TIME in .env file and restart.');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to parse time: {$e->getMessage()}");
            $this->info('');
            $this->info('Valid formats:');
            $this->info('  Absolute: 2026-01-15 12:00:00');
            $this->info('  Relative: +2 hours, +1 day, tomorrow, etc.');
            return Command::FAILURE;
        }
    }
}
