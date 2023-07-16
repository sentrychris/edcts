<?php

use App\Models\Commander;
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
        Schema::create('flight_log', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Commander::class)->constrained();
            $table->foreignIdFor(System::class)->constrained();
            $table->boolean('first_discover')->default(false);
            $table->timestamp('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flight_log');
    }
};
