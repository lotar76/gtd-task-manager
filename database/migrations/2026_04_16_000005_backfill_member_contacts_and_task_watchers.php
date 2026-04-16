<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * После перехода «одно пространство на пользователя» задачи из общих пространств
 * должны оставаться видимыми участникам. Для этого:
 *
 *  (A) все члены одного workspace становятся взаимными контактами;
 *  (B) в каждой задаче workspace'а все члены кроме автора помечаются наблюдателями
 *      через task_contact.role='watcher' (по контакту creator → member).
 */
return new class extends Migration
{
    public function up(): void
    {
        // (A) member↔member контакты
        foreach (DB::table('workspaces')->get() as $ws) {
            $memberIds = DB::table('workspace_members')
                ->where('workspace_id', $ws->id)
                ->pluck('user_id')
                ->toArray();
            $users = array_values(array_unique(array_merge($memberIds, [$ws->owner_id])));
            foreach ($users as $a) {
                foreach ($users as $b) {
                    if ($a === $b) continue;
                    $name = DB::table('users')->where('id', $b)->value('name') ?? 'Unknown';
                    DB::table('contacts')->insertOrIgnore([
                        'owner_id' => $a,
                        'contact_user_id' => $b,
                        'name' => $name,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // (B) watcher task_contact для задач в общих пространствах
        foreach (DB::table('workspaces')->get() as $ws) {
            $memberIds = DB::table('workspace_members')
                ->where('workspace_id', $ws->id)
                ->pluck('user_id')
                ->toArray();
            $allUsers = array_values(array_unique(array_merge($memberIds, [$ws->owner_id])));
            if (count($allUsers) <= 1) continue;

            $tasks = DB::table('tasks')->where('workspace_id', $ws->id)->get(['id', 'created_by']);
            foreach ($tasks as $task) {
                foreach ($allUsers as $uid) {
                    if ($uid === $task->created_by) continue;
                    $contact = DB::table('contacts')
                        ->where('owner_id', $task->created_by)
                        ->where('contact_user_id', $uid)
                        ->first();
                    if (!$contact) continue;
                    DB::table('task_contact')->insertOrIgnore([
                        'task_id' => $task->id,
                        'contact_id' => $contact->id,
                        'role' => 'watcher',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // Ничего не откатываем — данные смешаны с прочими.
    }
};
