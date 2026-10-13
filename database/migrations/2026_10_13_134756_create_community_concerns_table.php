<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('community_concerns', function (Blueprint $table) {
        $table->id();

        $table->foreignId('resident_id')
            ->constrained('residents')
            ->cascadeOnDelete();

        $table->string('subject');
        $table->text('description');
        $table->string('category')->nullable();
        $table->string('status')->default('Open');
        $table->text('resolution')->nullable();

        $table->foreignId('handled_by')
            ->nullable()
            ->constrained('users')
            ->nullOnDelete();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('role');
    });
}
};
