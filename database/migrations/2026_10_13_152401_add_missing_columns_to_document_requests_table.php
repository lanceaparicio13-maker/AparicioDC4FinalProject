<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('resident_id');
            $table->unsignedBigInteger('document_type_id');
            $table->text('purpose');
            $table->string('status')->default('Pending');
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('released_at')->nullable();
        });

        Schema::table('document_requests', function (Blueprint $table) {
            $table->foreign('resident_id')
                ->references('id')->on('residents')
                ->cascadeOnDelete();

            $table->foreign('document_type_id')
                ->references('id')->on('document_types')
                ->restrictOnDelete();

            $table->foreign('processed_by')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('document_requests', function (Blueprint $table) {
            $table->dropForeign(['resident_id']);
            $table->dropForeign(['document_type_id']);
            $table->dropForeign(['processed_by']);

            $table->dropColumn([
                'resident_id',
                'document_type_id',
                'purpose',
                'status',
                'remarks',
                'processed_by',
                'requested_at',
                'released_at',
            ]);
        });
    }
};