<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RecalculateTaskStatuses extends Command
{
    protected $signature = 'tasks:recalculate-statuses';
    protected $description = 'Пересчитывает статусы задач на основе due_date (выполняется в 00:00 по МСК)';

    public function handle()
    {
        // Устанавливаем московское время
        $moscowTime = Carbon::now('Europe/Moscow');
        $today = $moscowTime->format('Y-m-d');
        $tomorrow = $moscowTime->copy()->addDay()->format('Y-m-d');
        
        $this->info("Московское время: {$moscowTime->format('Y-m-d H:i:s')}");
        $this->info("Сегодня: $today");
        $this->info("Завтра: $tomorrow");
        $this->newLine();

        $updatedCount = 0;

        // 1. Задачи со статусом "today" - если due_date != сегодня, переводим в scheduled
        $todayTasks = DB::table('tasks')
            ->where('status', 'today')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '!=', $today)
            ->where('status', '!=', 'completed')
            ->get(['id', 'title', 'due_date']);

        if ($todayTasks->count() > 0) {
            $count = DB::table('tasks')
                ->where('status', 'today')
                ->whereNotNull('due_date')
                ->whereDate('due_date', '!=', $today)
                ->where('status', '!=', 'completed')
                ->update(['status' => 'scheduled']);
            
            $updatedCount += $count;
            $this->info("Обновлено задач 'today' → 'scheduled': $count");
        }

        // 2. Задачи со статусом "tomorrow" 
        //    - если due_date = сегодня → переводим в today
        //    - если due_date != завтра и != сегодня → переводим в scheduled
        $tomorrowTasksToday = DB::table('tasks')
            ->where('status', 'tomorrow')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '=', $today)
            ->where('status', '!=', 'completed')
            ->count();

        if ($tomorrowTasksToday > 0) {
            $count = DB::table('tasks')
                ->where('status', 'tomorrow')
                ->whereNotNull('due_date')
                ->whereDate('due_date', '=', $today)
                ->where('status', '!=', 'completed')
                ->update(['status' => 'today']);
            
            $updatedCount += $count;
            $this->info("Обновлено задач 'tomorrow' → 'today': $count");
        }

        $tomorrowTasksScheduled = DB::table('tasks')
            ->where('status', 'tomorrow')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '!=', $today)
            ->whereDate('due_date', '!=', $tomorrow)
            ->where('status', '!=', 'completed')
            ->count();

        if ($tomorrowTasksScheduled > 0) {
            $count = DB::table('tasks')
                ->where('status', 'tomorrow')
                ->whereNotNull('due_date')
                ->whereDate('due_date', '!=', $today)
                ->whereDate('due_date', '!=', $tomorrow)
                ->where('status', '!=', 'completed')
                ->update(['status' => 'scheduled']);
            
            $updatedCount += $count;
            $this->info("Обновлено задач 'tomorrow' → 'scheduled': $count");
        }

        // 3. Задачи со статусом "scheduled"
        //    - если due_date = сегодня → переводим в today
        //    - если due_date = завтра → переводим в tomorrow
        $scheduledTasksToday = DB::table('tasks')
            ->where('status', 'scheduled')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '=', $today)
            ->where('status', '!=', 'completed')
            ->count();

        if ($scheduledTasksToday > 0) {
            $count = DB::table('tasks')
                ->where('status', 'scheduled')
                ->whereNotNull('due_date')
                ->whereDate('due_date', '=', $today)
                ->where('status', '!=', 'completed')
                ->update(['status' => 'today']);
            
            $updatedCount += $count;
            $this->info("Обновлено задач 'scheduled' → 'today': $count");
        }

        $scheduledTasksTomorrow = DB::table('tasks')
            ->where('status', 'scheduled')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '=', $tomorrow)
            ->where('status', '!=', 'completed')
            ->count();

        if ($scheduledTasksTomorrow > 0) {
            $count = DB::table('tasks')
                ->where('status', 'scheduled')
                ->whereNotNull('due_date')
                ->whereDate('due_date', '=', $tomorrow)
                ->where('status', '!=', 'completed')
                ->update(['status' => 'tomorrow']);
            
            $updatedCount += $count;
            $this->info("Обновлено задач 'scheduled' → 'tomorrow': $count");
        }

        $this->newLine();
        $this->info("Всего обновлено задач: $updatedCount");

        return Command::SUCCESS;
    }
}

