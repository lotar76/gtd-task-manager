<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contexts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->onDelete('cascade');
            $table->string('name'); // @home, @work, @phone, @email, etc.
            $table->string('icon')->nullable();
            $table->string('color', 7)->default('#8B5CF6');
            $table->timestamps();
            
            $table->index('workspace_id');
            $table->unique(['workspace_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contexts');
    }
};


