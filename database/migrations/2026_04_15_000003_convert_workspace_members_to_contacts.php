<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Конвертируем workspace_members в контакты
        // Для каждого владельца workspace — все участники становятся его контактами
        $members = DB::table('workspace_members')
            ->join('workspaces', 'workspaces.id', '=', 'workspace_members.workspace_id')
            ->select('workspaces.owner_id', 'workspace_members.user_id', 'workspace_members.workspace_id')
            ->where('workspace_members.user_id', '!=', DB::raw('workspaces.owner_id'))
            ->get();

        $contactsMap = []; // [owner_id:contact_user_id => contact_id]

        foreach ($members as $member) {
            $key = $member->owner_id . ':' . $member->user_id;

            if (isset($contactsMap[$key])) {
                continue;
            }

            $userName = DB::table('users')
                ->where('id', $member->user_id)
                ->value('name') ?? 'Unknown';

            $contactId = DB::table('contacts')->insertGetId([
                'owner_id' => $member->owner_id,
                'contact_user_id' => $member->user_id,
                'name' => $userName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $contactsMap[$key] = $contactId;

            // Обратная связь: участник тоже получает владельца как контакт
            $reverseKey = $member->user_id . ':' . $member->owner_id;
            if (!isset($contactsMap[$reverseKey])) {
                $ownerName = DB::table('users')
                    ->where('id', $member->owner_id)
                    ->value('name') ?? 'Unknown';

                $reverseContactId = DB::table('contacts')->insertGetId([
                    'owner_id' => $member->user_id,
                    'contact_user_id' => $member->owner_id,
                    'name' => $ownerName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $contactsMap[$reverseKey] = $reverseContactId;
            }
        }

        // 2. Для задач в общих пространствах — участники становятся наблюдателями
        foreach ($members as $member) {
            $key = $member->owner_id . ':' . $member->user_id;
            $contactId = $contactsMap[$key] ?? null;

            if (!$contactId) {
                continue;
            }

            // Все задачи в этом workspace, где создатель — владелец workspace
            $taskIds = DB::table('tasks')
                ->where('workspace_id', $member->workspace_id)
                ->where('created_by', $member->owner_id)
                ->pluck('id');

            foreach ($taskIds as $taskId) {
                DB::table('task_contact')->insertOrIgnore([
                    'task_id' => $taskId,
                    'contact_id' => $contactId,
                    'role' => 'informed',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Очищаем сконвертированные данные
        DB::table('task_contact')->truncate();
        DB::table('contacts')->truncate();
    }
};
