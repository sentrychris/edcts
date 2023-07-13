<?php

use App\Models\Commander;
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
            $table->string('name');
            $table->foreignIdFor(Commander::class)->constrained();
            $table->string('identifier')->unique();
            $table->boolean('has_refuel')->default(false);
            $table->boolean('has_repair')->default(false);
            $table->boolean('has_armory')->default(false);
            $table->boolean('has_shipyard')->default(false);
            $table->boolean('has_outfitting')->default(false);
            $table->boolean('has_cartographics')->default(false);
            $table->timestamps();
            $table->softDeletes();
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
