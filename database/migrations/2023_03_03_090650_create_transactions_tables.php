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
        if (! Schema::hasTable('transactions')) {
            Schema::create('transactions', static function (Blueprint $table) {
                $table->id();
                $table->foreignId('account_id')
                    ->constrained('accounts')
                    ->cascadeOnDelete();

                $table->foreignId('related_id')
                    ->nullable()
                    ->constrained('transactions')
                    ->cascadeOnDelete();

                $table->foreignId('ledger_id')
                    ->constrained('ledgers')
                    ->cascadeOnDelete();

                $table->foreignId('category_id')->constrained('categories');

                $table->boolean('is_approved')->default(true);
                $table->boolean('is_cleared')->default(true);
                $table->boolean('is_excluded')->default(false);
                $table->string('remarks')->nullable();
                $table->bigInteger('outflow')->default(0);
                $table->bigInteger('inflow')->default(0);
                $table->date('transaction_date');
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('rejected_at')->nullable();
                $table->timestamp('cleared_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['ledger_id', 'transaction_date', 'category_id']);
                $table->index(['account_id', 'transaction_date']);
                $table->index(['account_id', 'category_id']);
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
        Schema::dropIfExists('transactions');
    }
};
