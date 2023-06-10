<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (! Schema::hasTable('budgets')) {
            Schema::create('budgets', static function (Blueprint $table) {
                $table->id();
                $table->foreignId('ledger_id')->constrained('ledgers');

                $table->tinyInteger('month')->nullable();
                $table->smallInteger('year')->nullable();

                $table->date('date');

                $table->timestamps();
                $table->softDeletes();

                $table->index(['date', 'ledger_id']);
                $table->index(['month', 'year', 'ledger_id']);
            });
        }

        if (! Schema::hasTable('budget_categories')) {
            Schema::create('budget_categories', static function (Blueprint $table) {
                $table->id();
                $table->foreignId('budget_id')
                    ->constrained('budgets')
                    ->cascadeOnDelete();

                $table->foreignId('category_id')
                    ->constrained('categories')
                    ->cascadeOnDelete();

                $table->bigInteger('assigned')->default(0);
                $table->bigInteger('available')->default(0);
                $table->bigInteger('activity')->default(0);

                $table->softDeletes();

                $table->index(['budget_id', 'category_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_categories');
        Schema::dropIfExists('budgets');
    }
};
