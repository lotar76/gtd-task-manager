<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('life_spheres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('color', 7)->default('#3B82F6');
            $table->unsignedInteger('position')->default(0);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index('workspace_id');
        });

        Schema::table('goals', function (Blueprint $table) {
            $table->foreignId('life_sphere_id')->nullable()->after('workspace_id')
                ->constrained('life_spheres')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('goals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('life_sphere_id');
        });

        Schema::dropIfExists('life_spheres');
    }
};
