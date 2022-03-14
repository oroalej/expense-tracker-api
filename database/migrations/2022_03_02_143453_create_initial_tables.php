<?php

use App\Enums\WalletAccessTypeState;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (!Schema::hasTable('tags')) {
            Schema::create('tags', static function (Blueprint $table) {
                $table->id();
                $table
                    ->foreignId('user_id')
                    ->constrained()
                    ->cascadeOnDelete();
                $table->string('name');
                $table->string('description')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('wallets')) {
            Schema::create('wallets', static function (Blueprint $table) {
                $table->id();
                $table->uuid();
                $table->string('name');
                $table->string('description')->nullable();
                $table->double('current_balance')->default(0);
                $table->unsignedTinyInteger('wallet_type');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('user_wallet')) {
            Schema::create('user_wallet', static function (Blueprint $table) {
                $table->id();
                $table
                    ->foreignId('wallet_id')
                    ->constrained()
                    ->cascadeOnDelete();
                $table
                    ->foreignId('user_id')
                    ->constrained()
                    ->cascadeOnDelete();
                $table
                    ->unsignedTinyInteger('access_type')
                    ->default(WalletAccessTypeState::Owner->value);
                $table->date('start_date')->default(DB::RAW('CURRENT_DATE'));
                $table->date('end_date')->nullable();
            });
        }

        if (!Schema::hasTable('categories')) {
            Schema::create('categories', static function (Blueprint $table) {
                $table->id();
                $table
                    ->foreignId('user_id')
                    ->constrained()
                    ->cascadeOnDelete();
                $table->unsignedTinyInteger('category_type');
                $table
                    ->foreignId('parent_id')
                    ->nullable()
                    ->constrained('categories');
                $table->string('name');
                $table->string('description')->nullable();
                $table->boolean('is_default')->default(false);
                $table->boolean('is_editable')->default(true);

                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('transactions')) {
            Schema::create('transactions', static function (Blueprint $table) {
                $table->id();
                $table->uuid();
                $table
                    ->foreignId('user_id')
                    ->constrained()
                    ->cascadeOnDelete();
                $table->foreignId('wallet_id')->constrained();
                $table->foreignId('category_id')->constrained();
                $table->double('amount');
                $table->string('remarks');
                $table->date('transaction_date');

                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('tag_transaction')) {
            Schema::create('tag_transaction', static function (
                Blueprint $table
            ) {
                $table->id();
                $table
                    ->foreignId('transaction_id')
                    ->constrained()
                    ->cascadeOnDelete();
                $table
                    ->foreignId('tag_id')
                    ->constrained()
                    ->cascadeOnDelete();
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
        Schema::dropIfExists('tag_transaction');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('categories');

        Schema::dropIfExists('user_wallet');
        Schema::dropIfExists('wallets');
        Schema::dropIfExists('tags');
    }
};
