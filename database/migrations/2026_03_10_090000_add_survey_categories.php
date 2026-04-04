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
        Schema::create('survey_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::table('survey_questions', function (Blueprint $table): void {
            $table->foreignId('survey_category_id')
                ->nullable()
                ->after('survey_id')
                ->constrained('survey_categories')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('survey_questions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('survey_category_id');
        });

        Schema::dropIfExists('survey_categories');
    }
};
