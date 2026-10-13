<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('residents', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->unique()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->date('birth_date')->nullable();
            $table->string('sex')->nullable();
            $table->string('civil_status')->nullable();
            $table->string('contact_number', 30)->nullable();
            $table->text('address');
            $table->string('purok')->nullable();
            $table->string('residency_status')->default('Active');
        });
    }

    public function down(): void
    {
        Schema::table('residents', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id']);

            $table->dropColumn([
                'user_id',
                'first_name',
                'middle_name',
                'last_name',
                'birth_date',
                'sex',
                'civil_status',
                'contact_number',
                'address',
                'purok',
                'residency_status',
            ]);
        });
    }
};