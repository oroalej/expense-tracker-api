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
        if (! Schema::hasTable('account_types')) {
            Schema::create('account_types', static function (Blueprint $table) {
                $table->id();
                $table->foreignId('group_type_id')->constrained('terms');

                $table->string('name');
                $table->tinyText('description')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('accounts')) {
            Schema::create('accounts', static function (Blueprint $table) {
                $table->id();
                $table
                    ->foreignId('ledger_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('account_type_id')->constrained('account_types');

                $table->string('name');
                $table->bigInteger('current_balance')->default(0);
                $table->boolean('is_archived')->default(false);
                $table->timestamp('archived_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('account_monthly_summary')) {
            Schema::create('account_monthly_summary', static function (Blueprint $table) {
                $table->id();

                $table->foreignId('account_id')->constrained('accounts');
                $table->bigInteger('total_uncleared_balance')->default(0);
                $table->bigInteger('total_cleared_balance')->default(0);
                $table->bigInteger('total_working_balance')->default(0);
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
        Schema::dropIfExists('account_monthly_summary');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('account_types');
    }
};
