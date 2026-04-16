<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Для всех контактов, связанных с пользователем, подтягиваем name и email из users.
        // Эти поля считаются "данными связанного пользователя" и далее readonly для owner'а.
        DB::statement('
            UPDATE contacts c
            JOIN users u ON u.id = c.contact_user_id
            SET c.name = u.name,
                c.email = COALESCE(NULLIF(c.email, ""), u.email)
            WHERE c.contact_user_id IS NOT NULL
        ');
    }

    public function down(): void
    {
        // Ничего не откатываем — данные уже были в users, значения в contacts остаются.
    }
};
