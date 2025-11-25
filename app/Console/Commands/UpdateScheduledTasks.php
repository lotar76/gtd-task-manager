<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateScheduledTasks extends Command
{
    protected $signature = 'tasks:update-scheduled';
    protected $description = 'Обновить статус задач с датами (не today/tomorrow) на scheduled';

    public function handle()
    {
        $today = now()->format('Y-m-d');
        $tomorrow = now()->addDay()->format('Y-m-d');

        $this->info("Сегодня: $today");
        $this->info("Завтра: $tomorrow");
        $this->newLine();

        // Проверяем задачи с датами (не today/tomorrow)
        $tasks = DB::table('tasks')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '!=', $today)
            ->whereDate('due_date', '!=', $tomorrow)
            ->whereNotIn('status', ['completed', 'today', 'tomorrow', 'scheduled'])
            ->get(['id', 'title', 'due_date', 'status']);

        $this->info("Найдено задач для обновления: " . $tasks->count());

        if ($tasks->count() > 0) {
            $this->newLine();
            $this->table(
                ['ID', 'Название', 'Дата', 'Статус'],
                $tasks->map(function ($task) {
                    return [
                        $task->id,
                        substr($task->title, 0, 30),
                        $task->due_date,
                        $task->status,
                    ];
                })->toArray()
            );

            $this->newLine();
            $this->info("Обновляю статус на 'scheduled'...");

            $count = DB::table('tasks')
                ->whereNotNull('due_date')
                ->whereDate('due_date', '!=', $today)
                ->whereDate('due_date', '!=', $tomorrow)
                ->whereNotIn('status', ['completed', 'today', 'tomorrow', 'scheduled'])
                ->update(['status' => 'scheduled']);

            $this->info("Обновлено задач: $count");
        } else {
            $this->info("Нет задач для обновления.");
        }

        $this->newLine();
        $scheduled = DB::table('tasks')->where('status', 'scheduled')->count();
        $this->info("Всего задач со статусом 'scheduled': $scheduled");

        return Command::SUCCESS;
    }
}

