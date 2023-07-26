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
            $table->foreignIdFor(System::class)->constrained();
            $table->string('name');
            $table->string('discovered_by');
            $table->timestamp('discovered_at');
            $table->string('type');
            $table->string('sub_type');
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
