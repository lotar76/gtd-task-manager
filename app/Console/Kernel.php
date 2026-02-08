<?php

declare(strict_types=1);

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Пересчет статусов задач в 00:00 по московскому времени
        $schedule->command('tasks:recalculate-statuses')
            ->dailyAt('00:00')
            ->timezone('Europe/Moscow');

        // Telegram: утренний дайджест (проверяет каждую минуту, шлёт по настройке времени)
        $schedule->command('telegram:morning-digest')
            ->everyMinute()
            ->timezone('Europe/Moscow');

        // Telegram: напоминания о задачах (проверяет каждую минуту)
        $schedule->command('telegram:task-reminders')
            ->everyMinute()
            ->timezone('Europe/Moscow');

        // Telegram: просроченные задачи (раз в день в 09:00 МСК)
        $schedule->command('telegram:overdue-alerts')
            ->dailyAt('09:00')
            ->timezone('Europe/Moscow');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

