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
        Schema::table('commanders', function (Blueprint $table) {
            $table->bigInteger('credits')->nullable()->after('cmdr_name');
            $table->bigInteger('debt')->nullable()->after('credits');
            $table->boolean('alive')->nullable()->after('debt');
            $table->boolean('docked')->nullable()->after('alive');
            $table->boolean('onfoot')->nullable()->after('docked');
            $table->json('rank')->nullable()->after('onfoot');
            $table->unsignedBigInteger('last_system_id64')->nullable()->after('rank');
            $table->foreign('last_system_id64')->references('id64')->on('systems');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commanders', function (Blueprint $table) {
            $table->dropForeign(['last_system_id64']);
            $table->dropColumn('credits');
            $table->dropColumn('debt');
            $table->dropColumn('alive');
            $table->dropColumn('docked');
            $table->dropColumn('onfoot');
            $table->dropColumn('rank');
            $table->dropColumn('last_system_id64');
        });
    }
};
