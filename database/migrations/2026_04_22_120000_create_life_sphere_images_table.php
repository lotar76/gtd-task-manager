<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('life_sphere_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('life_sphere_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        // Migrate existing images
        $spheres = DB::table('life_spheres')->whereNotNull('image')->get();
        foreach ($spheres as $sphere) {
            DB::table('life_sphere_images')->insert([
                'life_sphere_id' => $sphere->id,
                'path' => $sphere->image,
                'position' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('life_spheres', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }

    public function down(): void
    {
        Schema::table('life_spheres', function (Blueprint $table) {
            $table->string('image')->nullable()->after('description');
        });

        // Migrate back cover images
        $images = DB::table('life_sphere_images')->where('position', 0)->get();
        foreach ($images as $image) {
            DB::table('life_spheres')->where('id', $image->life_sphere_id)->update(['image' => $image->path]);
        }

        Schema::dropIfExists('life_sphere_images');
    }
};
