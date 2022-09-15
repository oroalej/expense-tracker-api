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
        if (! Schema::hasTable('taxonomies')) {
            Schema::create('taxonomies', static function (Blueprint $table) {
                $table->id();
                $table->string('name');
            });
        }

        if (! Schema::hasTable('terms')) {
            Schema::create('terms', static function (Blueprint $table) {
                $table->id();
                $table->uuid();
                $table->foreignId('taxonomy_id')->constrained();

                $table->string('name');

                $table->timestamps();

                $table->index('taxonomy_id');
            });
        }

        if (! Schema::hasTable('currencies')) {
            Schema::create('currencies', static function (Blueprint $table) {
                $table->id();
                $table->uuid();
                $table->string('name');
                $table->string('abbr');
                $table->string('code');
                $table->string('locale');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ledgers')) {
            Schema::create('ledgers', static function (Blueprint $table) {
                $table->id();
                $table->uuid();
                $table->foreignId('user_id')->constrained();
//                $table->foreignId('date_format_id')->constrained('terms');
//                $table->foreignId('currency_placement_id')->constrained('terms');
//                $table->foreignId('number_format_id')->constrained('terms');
//                $table->foreignId('currency_id')->constrained('currencies');

                $table->string('name');
//                $table->tinyInteger('currency_placement');
                $table->boolean('is_archived')->default(false);

                $table->timestamp('archived_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index('uuid');
            });
        }

        if (! Schema::hasTable('category_groups')) {
            Schema::create('category_groups', static function (
                Blueprint $table
            ) {
                $table->id();
                $table->uuid();
                $table
                    ->foreignId('ledger_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->string('name');
                $table->string('notes')->nullable();
                $table->boolean('is_hidden')->default(false);
                $table->timestamps();
                $table->softDeletes();

                $table->index('uuid');
            });
        }

        if (! Schema::hasTable('categories')) {
            Schema::create('categories', static function (Blueprint $table) {
                $table->id();
                $table->uuid();
                $table
                    ->foreignId('category_group_id')
                    ->constrained();

                $table->string('name');
                $table->string('notes')->nullable();
                $table->boolean('is_hidden')->default(false);

                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('account_types')) {
            Schema::create('account_types', static function (Blueprint $table) {
                $table->id();
                $table->uuid();
                $table->foreignId('group_type_id')->constrained('terms');

                $table->string('name');
                $table->tinyText('description')->nullable();
                $table->timestamps();

                $table->index('uuid');
            });
        }

        if (! Schema::hasTable('accounts')) {
            Schema::create('accounts', static function (Blueprint $table) {
                $table->id();
                $table->uuid();
                $table
                    ->foreignId('ledger_id')
                    ->constrained()
                    ->cascadeOnDelete();
                $table->foreignId('account_type_id')->constrained();

                $table->string('name');
                $table->decimal('current_balance', 13, 4)->default(0);
                $table->boolean('is_archived')->default(false);

                $table->timestamp('archived_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index('uuid');
            });
        }

        if (! Schema::hasTable('debts')) {
            Schema::create('debts', static function (Blueprint $table) {
                $table->id();
                $table->uuid();
                $table->foreignId('ledger_id')->constrained();
                $table->foreignId('payment_interval_id')->constrained('terms');

                $table->string('name');
                $table->tinyText('notes')->nullable();
                $table->decimal('current_balance', 13, 4)->default(0);
                $table->decimal('interest_rate', 13, 4)->default(0);
                $table->decimal('min_payment_amount', 13, 4)->default(0);
                $table->boolean('is_closed')->default(false);
                $table->timestamp('closed_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index('uuid');
            });
        }

        if (! Schema::hasTable('goals')) {
            Schema::create('goals', static function (Blueprint $table) {
                $table->id();
                $table->uuid();
                $table->foreignId('ledger_id')->constrained();

                $table->string('name');
                $table->tinyText('notes')->nullable();
                $table->decimal('target_amount', 13, 4)->default(0);
                $table->decimal('current_balance', 13, 4)->default(0);
                $table->tinyInteger('month')->nullable();
                $table->tinyInteger('year')->nullable();

                $table->timestamps();
                $table->softDeletes();

                $table->index('uuid');
            });
        }

        if (! Schema::hasTable('transactions')) {
            Schema::create('transactions', static function (Blueprint $table) {
                $table->id();
                $table->uuid();
                $table
                    ->foreignId('account_id')
                    ->constrained()
                    ->cascadeOnDelete();
                $table->foreignId('related_id')
                    ->nullable()
                    ->constrained('transactions')
                    ->cascadeOnDelete();
                $table->foreignId('frequency_id')->nullable()->constrained('terms');
                $table->foreignId('category_id')->constrained();

                $table->boolean('is_approved')->default(true);
                $table->boolean('is_cleared')->default(true);
                $table->boolean('is_excluded')->default(false);
                $table->string('remarks')->nullable();
                $table->decimal('outflow', 13, 4, true)->nullable();
                $table->decimal('inflow', 13, 4, true)->nullable();
                $table->date('transaction_date');
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('rejected_at')->nullable();
                $table->timestamp('cleared_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index('is_excluded');
            });
        }

        if (! Schema::hasTable('budgets')) {
            Schema::create('budgets', static function (Blueprint $table) {
                $table->id();
                $table->uuid();
                $table->foreignId('ledger_id')->constrained();

                $table->tinyText('notes')->nullable();
                $table->tinyInteger('month')->nullable();
                $table->tinyInteger('year')->nullable();

                $table->timestamps();
                $table->softDeletes();

                $table->index('uuid');
            });
        }

        if (! Schema::hasTable('budget_category')) {
            Schema::create('budget_category', static function (Blueprint $table) {
                $table->id();
                $table->uuid();
                $table->foreignId('budget_id')->constrained();
                $table->foreignId('category_id')->constrained();

                $table->decimal('assigned', 13, 4, true)->nullable();
                $table->decimal('available', 13, 4, true)->nullable();
                $table->decimal('activity', 13, 4, true)->nullable();

                $table->timestamps();

                $table->index('uuid');
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

        Schema::dropIfExists('budget_category');
        Schema::dropIfExists('budgets');

        Schema::dropIfExists('categories');
        Schema::dropIfExists('category_groups');

        Schema::dropIfExists('goals');
        Schema::dropIfExists('debts');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('ledgers');

        Schema::dropIfExists('terms');
        Schema::dropIfExists('taxonomies');
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('permissions');
    }
};
