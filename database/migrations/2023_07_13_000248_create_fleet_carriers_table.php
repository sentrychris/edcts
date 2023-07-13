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
        Schema::create('fleet_carriers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained();
            $table->string('carrier_name');
            $table->boolean('has_refuel');
            $table->boolean('has_repair');
            $table->boolean('has_armory');
            $table->boolean('has_shipyard');
            $table->boolean('has_outfitting');
            $table->boolean('has_cartographics');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fleet_carriers');
    }
};
