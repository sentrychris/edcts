<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Converts coords_x/y/z from stored generated columns (derived from the JSON
     * coords column) to native float columns, then drops the JSON coords column.
     * Also removes the redundant composite (name, id64) and fulltext indexes on
     * name — the unique index and slug index are kept.
     */
    public function up(): void
    {
        // Drop the two redundant name indexes; keep systems_name_unique and systems_slug_index
        Schema::table('systems', function (Blueprint $table) {
            $table->dropIndex('systems_name_id64_index');
            $table->dropFullText('systems_name_fulltext');
        });

        // Add temporary native float columns to receive the copied coordinate data
        Schema::table('systems', function (Blueprint $table) {
            $table->float('coords_x_new')->nullable()->after('coords');
            $table->float('coords_y_new')->nullable()->after('coords_x_new');
            $table->float('coords_z_new')->nullable()->after('coords_y_new');
        });

        // Copy data from the generated columns into the new regular columns
        DB::statement('UPDATE systems SET coords_x_new = coords_x, coords_y_new = coords_y, coords_z_new = coords_z');

        // Drop the generated column index, the generated columns, and the JSON coords column
        Schema::table('systems', function (Blueprint $table) {
            $table->dropIndex('systems_coords_xyz_index');
            $table->dropColumn(['coords_x', 'coords_y', 'coords_z', 'coords']);
        });

        // Rename the temp columns to their final names and enforce NOT NULL
        DB::statement('ALTER TABLE systems CHANGE coords_x_new coords_x FLOAT NOT NULL, CHANGE coords_y_new coords_y FLOAT NOT NULL, CHANGE coords_z_new coords_z FLOAT NOT NULL');

        // Recreate the bounding-box index on the now-native float columns
        Schema::table('systems', function (Blueprint $table) {
            $table->index(['coords_x', 'coords_y', 'coords_z'], 'systems_coords_xyz_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore the JSON coords column and populate it from the native float columns
        Schema::table('systems', function (Blueprint $table) {
            $table->json('coords')->nullable()->after('name');
        });

        DB::statement("UPDATE systems SET coords = JSON_OBJECT('x', coords_x, 'y', coords_y, 'z', coords_z)");
        DB::statement('ALTER TABLE systems MODIFY coords JSON NOT NULL');

        // Drop the native xyz columns (and their index) and restore as stored generated columns
        Schema::table('systems', function (Blueprint $table) {
            $table->dropIndex('systems_coords_xyz_index');
            $table->dropColumn(['coords_x', 'coords_y', 'coords_z']);
        });

        Schema::table('systems', function (Blueprint $table) {
            $table->float('coords_x')->storedAs("JSON_EXTRACT(coords, '$.x')")->after('coords');
            $table->float('coords_y')->storedAs("JSON_EXTRACT(coords, '$.y')")->after('coords_x');
            $table->float('coords_z')->storedAs("JSON_EXTRACT(coords, '$.z')")->after('coords_y');
            $table->index(['coords_x', 'coords_y', 'coords_z'], 'systems_coords_xyz_index');
        });

        // Restore the two redundant name indexes
        Schema::table('systems', function (Blueprint $table) {
            $table->fullText('name');
            $table->index(['name', 'id64']);
        });
    }
};
