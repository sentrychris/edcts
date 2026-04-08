<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds stored generated columns for x/y/z extracted from the coords JSON
     * and a compound index over them, enabling bounding-box pre-filtering on
     * spatial queries instead of a full table scan.
     */
    public function up(): void
    {
        Schema::table('systems', function (Blueprint $table) {
            $table->float('coords_x')->storedAs("JSON_EXTRACT(coords, '$.x')")->after('coords');
            $table->float('coords_y')->storedAs("JSON_EXTRACT(coords, '$.y')")->after('coords_x');
            $table->float('coords_z')->storedAs("JSON_EXTRACT(coords, '$.z')")->after('coords_y');

            $table->index(['coords_x', 'coords_y', 'coords_z'], 'systems_coords_xyz_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('systems', function (Blueprint $table) {
            $table->dropIndex('systems_coords_xyz_index');
            $table->dropColumn(['coords_x', 'coords_y', 'coords_z']);
        });
    }
};
