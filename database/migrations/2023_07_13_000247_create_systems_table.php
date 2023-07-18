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
        Schema::create('systems', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id64')->unique();
            $table->string('name')->unique();
            $table->string('main_star')->nullable();
            $table->json('coords');
            $table->string('slug')->nullable();
            $table->timestamp('updated_at');
            $table->softDeletes();
            $table->index(['name', 'id64', 'updated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('systems');
    }
};
