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
        if (! Schema::hasTable('categories')) {
            Schema::create('categories', static function (Blueprint $table) {
                $table->id();
                $table->foreignId('parent_id')
                    ->nullable()
                    ->references('id')
                    ->on('categories');

                $table->foreignId('ledger_id')
                    ->nullable()
                    ->references('id')
                    ->on('ledgers');

                $table->string('name');
                $table->string('notes')->nullable();
                $table->unsignedTinyInteger('category_type');
                $table->unsignedTinyInteger('order');
                $table->boolean('is_visible')->default(true);
                $table->boolean('is_budgetable')->default(true);
                $table->boolean('is_reportable')->default(true);
                $table->boolean('is_editable')->default(true);

                $table->timestamps();
                $table->softDeletes();

                $table->index(['ledger_id', 'category_type']);
                $table->index(['ledger_id', 'parent_id']);
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
        Schema::dropIfExists('categories');
    }
};
