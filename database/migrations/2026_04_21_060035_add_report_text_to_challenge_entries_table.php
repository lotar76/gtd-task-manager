<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('challenge_entries', function (Blueprint $table) {
            $table->text('report_text')->nullable()->after('timer_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('challenge_entries', function (Blueprint $table) {
            $table->dropColumn('report_text');
        });
    }
};
