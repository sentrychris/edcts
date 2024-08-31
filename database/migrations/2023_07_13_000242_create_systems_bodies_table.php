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
        Schema::create('systems_bodies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id64')->unique();
            $table->unsignedBigInteger('body_id');
            $table->foreignIdFor(System::class)->constrained();
            $table->string('name');
            $table->string('discovered_by')->nullable();
            $table->timestamp('discovered_at')->nullable();
            $table->string('type');
            $table->string('sub_type');
            $table->bigInteger('distance_to_arrival')->nullable();
            $table->boolean('is_main_star')->nullable();
            $table->boolean('is_scoopable')->nullable();
            $table->string('spectral_class')->nullable();
            $table->string('luminosity')->nullable();
            $table->double('solar_masses')->nullable();
            $table->double('solar_radius')->nullable();
            $table->double('absolute_magnitude')->nullable();
            $table->bigInteger('surface_temp')->nullable();
            $table->double('radius')->nullable();
            $table->double('gravity')->nullable();
            $table->double('earth_masses')->nullable();
            $table->string('atmosphere_type')->nullable();
            $table->string('volcanism_type')->nullable();
            $table->string('terraforming_state')->nullable();
            $table->boolean('is_landable')->default(false);
            $table->double('orbital_period')->nullable();
            $table->double('orbital_eccentricity')->nullable();
            $table->double('orbital_inclination')->nullable();
            $table->double('arg_of_periapsis')->nullable();
            $table->double('rotational_period')->nullable();
            $table->boolean('is_tidally_locked')->default(false);
            $table->double('semi_major_axis')->nullable();
            $table->double('axial_tilt')->nullable();
            $table->json('rings')->nullable();
            $table->json('parents')->nullable();
            $table->string('slug')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('systems_bodies');
    }
};
