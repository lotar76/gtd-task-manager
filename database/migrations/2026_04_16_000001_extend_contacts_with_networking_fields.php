<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->boolean('is_favorite')->default(false)->after('owner_id');
            $table->string('contact_type', 32)->default('regular')->after('is_favorite');
            $table->string('specialization')->nullable()->after('contact_type');
            $table->string('personal_phone')->nullable()->after('phone');
            $table->string('personal_email')->nullable()->after('personal_phone');
            $table->json('messengers')->nullable()->after('personal_email');
            $table->string('address')->nullable()->after('messengers');

            $table->index('is_favorite');
            $table->index('contact_type');
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex(['is_favorite']);
            $table->dropIndex(['contact_type']);
            $table->dropColumn([
                'is_favorite',
                'contact_type',
                'specialization',
                'personal_phone',
                'personal_email',
                'messengers',
                'address',
            ]);
        });
    }
};
