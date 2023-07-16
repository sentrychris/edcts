<?php

use App\Models\System;
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
        Schema::create('systems_information', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(System::class)->unique()->constrained();
            $table->string('allegiance')->nullable();
            $table->string('government')->nullable();
            $table->string('faction')->nullable();
            $table->string('faction_state')->nullable();
            $table->unsignedBigInteger('population')->default(0);
            $table->string('security')->nullable();
            $table->string('economy')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('systems_information');
    }
};
