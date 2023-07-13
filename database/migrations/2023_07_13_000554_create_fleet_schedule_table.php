<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\FleetCarrier;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fleet_schedule', function (Blueprint $table) {
            $table->id();
            $table->foreignIdfor(FleetCarrier::class)->constrained();
            $table->string('departure');
            $table->string('destination');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('departs_at');
            $table->timestamp('departed_at')->nullable();
            $table->timestamp('arrives_at')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->boolean('is_boarding')->default(false);
            $table->boolean('is_cancelled')->default(false);
            $table->boolean('has_departed')->default(false);
            $table->boolean('has_arrived')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departures');
    }
};
