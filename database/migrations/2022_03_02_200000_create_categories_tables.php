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
        if (! Schema::hasTable('category_groups')) {
            Schema::create('category_groups', static function (Blueprint $table) {
                $table->id();
                $table->string('hashid')->nullable();
                $table
                    ->foreignId('ledger_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->string('name');
                $table->string('notes')->nullable();
                $table->boolean('is_hidden')->default(false);
                $table->tinyInteger('order');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('categories')) {
            Schema::create('categories', static function (Blueprint $table) {
                $table->id();
                $table->string('hashid')->nullable();
                $table
                    ->foreignId('category_group_id')
                    ->constrained();

                $table->foreignId('ledger_id')
                    ->constrained();

                $table->string('name');
                $table->string('notes')->nullable();
                $table->boolean('is_hidden')->default(false);
                $table->tinyInteger('order');

                $table->timestamps();
                $table->softDeletes();
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
        Schema::dropIfExists('category_groups');
    }
};
