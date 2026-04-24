<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('life_sphere_id')->nullable()->after('goal_id')->constrained('life_spheres')->nullOnDelete();
            $table->index('life_sphere_id');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['life_sphere_id']);
            $table->dropColumn('life_sphere_id');
        });
    }
};
