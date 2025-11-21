<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Context;
use App\Models\Goal;
use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GtdDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Создание тестовых пользователей
        $owner = User::create([
            'name' => 'Владимир Иванов',
            'email' => 'owner@example.com',
            'password' => Hash::make('password'),
        ]);

        $member1 = User::create([
            'name' => 'Анна Петрова',
            'email' => 'anna@example.com',
            'password' => Hash::make('password'),
        ]);

        $member2 = User::create([
            'name' => 'Сергей Сидоров',
            'email' => 'sergey@example.com',
            'password' => Hash::make('password'),
        ]);

        // Создание workspace
        $workspace = Workspace::create([
            'name' => 'Команда разработки',
            'slug' => 'dev-team',
            'description' => 'Рабочее пространство команды разработки',
            'owner_id' => $owner->id,
        ]);

        // Добавление участников
        $workspace->members()->attach($owner->id, ['role' => 'owner']);
        $workspace->members()->attach($member1->id, ['role' => 'admin']);
        $workspace->members()->attach($member2->id, ['role' => 'member']);

        // Создание контекстов GTD
        $contextHome = Context::create([
            'workspace_id' => $workspace->id,
            'name' => '@home',
            'icon' => 'home',
            'color' => '#10B981',
        ]);

        $contextWork = Context::create([
            'workspace_id' => $workspace->id,
            'name' => '@work',
            'icon' => 'briefcase',
            'color' => '#3B82F6',
        ]);

        $contextPhone = Context::create([
            'workspace_id' => $workspace->id,
            'name' => '@phone',
            'icon' => 'phone',
            'color' => '#8B5CF6',
        ]);

        $contextEmail = Context::create([
            'workspace_id' => $workspace->id,
            'name' => '@email',
            'icon' => 'mail',
            'color' => '#F59E0B',
        ]);

        // Создание тегов
        $tagUrgent = Tag::create([
            'workspace_id' => $workspace->id,
            'name' => 'Срочно',
            'color' => '#EF4444',
        ]);

        $tagBug = Tag::create([
            'workspace_id' => $workspace->id,
            'name' => 'Bug',
            'color' => '#DC2626',
        ]);

        $tagFeature = Tag::create([
            'workspace_id' => $workspace->id,
            'name' => 'Feature',
            'color' => '#3B82F6',
        ]);

        // Создание целей
        $goal1 = Goal::create([
            'workspace_id' => $workspace->id,
            'name' => 'Запуск нового продукта',
            'description' => 'Разработка и запуск GTD TODO трекера',
            'color' => '#3B82F6',
            'status' => 'active',
            'deadline' => now()->addMonths(3),
            'created_by' => $owner->id,
        ]);

        $goal2 = Goal::create([
            'workspace_id' => $workspace->id,
            'name' => 'Улучшение процессов',
            'description' => 'Оптимизация рабочих процессов команды',
            'color' => '#10B981',
            'status' => 'active',
            'deadline' => now()->addMonths(2),
            'created_by' => $owner->id,
        ]);

        // Создание проектов
        $project1 = Project::create([
            'workspace_id' => $workspace->id,
            'goal_id' => $goal1->id,
            'name' => 'Backend API',
            'description' => 'Разработка REST API на Laravel',
            'color' => '#3B82F6',
            'status' => 'active',
            'created_by' => $owner->id,
        ]);

        $project2 = Project::create([
            'workspace_id' => $workspace->id,
            'goal_id' => $goal1->id,
            'name' => 'Frontend Vue',
            'description' => 'Разработка фронтенда на Vue 3',
            'color' => '#10B981',
            'status' => 'active',
            'created_by' => $owner->id,
        ]);

        $project3 = Project::create([
            'workspace_id' => $workspace->id,
            'goal_id' => $goal2->id,
            'name' => 'Документация',
            'description' => 'Написание технической документации',
            'color' => '#F59E0B',
            'status' => 'active',
            'created_by' => $owner->id,
        ]);

        // Создание задач
        
        // Inbox (входящие)
        Task::create([
            'workspace_id' => $workspace->id,
            'title' => 'Проверить новые требования от клиента',
            'description' => 'Нужно изучить и декомпозировать',
            'status' => 'inbox',
            'priority' => 'medium',
            'created_by' => $owner->id,
            'assigned_to' => $owner->id,
            'position' => 1,
        ]);

        Task::create([
            'workspace_id' => $workspace->id,
            'title' => 'Разобрать почту',
            'status' => 'inbox',
            'priority' => 'low',
            'context_id' => $contextEmail->id,
            'created_by' => $owner->id,
            'assigned_to' => $owner->id,
            'position' => 2,
        ]);

        // Next Actions (следующие действия)
        $task1 = Task::create([
            'workspace_id' => $workspace->id,
            'project_id' => $project1->id,
            'title' => 'Реализовать авторизацию через Sanctum',
            'description' => 'Добавить регистрацию, логин, logout',
            'status' => 'next_action',
            'priority' => 'high',
            'context_id' => $contextWork->id,
            'due_date' => now()->addDays(2),
            'created_by' => $owner->id,
            'assigned_to' => $member1->id,
            'position' => 3,
        ]);
        $task1->tags()->attach([$tagFeature->id, $tagUrgent->id]);

        $task2 = Task::create([
            'workspace_id' => $workspace->id,
            'project_id' => $project1->id,
            'title' => 'Создать миграции для всех таблиц',
            'status' => 'next_action',
            'priority' => 'high',
            'context_id' => $contextWork->id,
            'due_date' => now()->addDay(),
            'created_by' => $owner->id,
            'assigned_to' => $member2->id,
            'position' => 4,
        ]);
        $task2->tags()->attach([$tagFeature->id]);

        Task::create([
            'workspace_id' => $workspace->id,
            'project_id' => $project2->id,
            'title' => 'Настроить Vue Router',
            'status' => 'next_action',
            'priority' => 'medium',
            'context_id' => $contextWork->id,
            'due_date' => now()->addDays(3),
            'created_by' => $owner->id,
            'assigned_to' => $member1->id,
            'position' => 5,
        ]);

        Task::create([
            'workspace_id' => $workspace->id,
            'project_id' => $project2->id,
            'title' => 'Создать компонент календаря',
            'description' => 'Календарный вид задач с фильтрами',
            'status' => 'next_action',
            'priority' => 'medium',
            'context_id' => $contextWork->id,
            'due_date' => now()->addDays(5),
            'created_by' => $owner->id,
            'assigned_to' => $member1->id,
            'position' => 6,
        ]);

        // Waiting (ожидание)
        Task::create([
            'workspace_id' => $workspace->id,
            'project_id' => $project1->id,
            'title' => 'Ожидание ревью кода от тимлида',
            'status' => 'waiting',
            'priority' => 'medium',
            'created_by' => $member2->id,
            'assigned_to' => $member2->id,
            'position' => 7,
        ]);

        Task::create([
            'workspace_id' => $workspace->id,
            'title' => 'Ответ от поддержки хостинга',
            'status' => 'waiting',
            'priority' => 'low',
            'context_id' => $contextEmail->id,
            'created_by' => $owner->id,
            'assigned_to' => $owner->id,
            'position' => 8,
        ]);

        // Someday (когда-нибудь)
        Task::create([
            'workspace_id' => $workspace->id,
            'title' => 'Изучить новый фреймворк',
            'status' => 'someday',
            'priority' => 'low',
            'context_id' => $contextHome->id,
            'created_by' => $member1->id,
            'assigned_to' => $member1->id,
            'position' => 9,
        ]);

        Task::create([
            'workspace_id' => $workspace->id,
            'title' => 'Написать статью в блог',
            'description' => 'О GTD методологии',
            'status' => 'someday',
            'priority' => 'low',
            'created_by' => $owner->id,
            'assigned_to' => $owner->id,
            'position' => 10,
        ]);

        // Completed (выполнено)
        Task::create([
            'workspace_id' => $workspace->id,
            'project_id' => $project1->id,
            'title' => 'Настроить Docker окружение',
            'status' => 'completed',
            'priority' => 'high',
            'context_id' => $contextWork->id,
            'completed_at' => now()->subDays(2),
            'created_by' => $owner->id,
            'assigned_to' => $owner->id,
            'position' => 11,
        ]);

        Task::create([
            'workspace_id' => $workspace->id,
            'project_id' => $project3->id,
            'title' => 'Создать README.md',
            'status' => 'completed',
            'priority' => 'medium',
            'completed_at' => now()->subDay(),
            'created_by' => $member2->id,
            'assigned_to' => $member2->id,
            'position' => 12,
        ]);

        $this->command->info('✅ Демо данные GTD созданы успешно!');
        $this->command->info('📧 Email владельца: owner@example.com');
        $this->command->info('📧 Email участника 1: anna@example.com');
        $this->command->info('📧 Email участника 2: sergey@example.com');
        $this->command->info('🔑 Пароль для всех: password');
    }
}


