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
                $table->foreignId('taxonomy_id')->constrained('taxonomies');
                $table->string('name');
            });
        }

        if (! Schema::hasTable('currencies')) {
            Schema::create('currencies', static function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('abbr');
                $table->string('code');
                $table->string('locale');
                $table->timestamps();
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
        Schema::dropIfExists('terms');
        Schema::dropIfExists('taxonomies');
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('permissions');
    }
};
