<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('layers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('geojson_file')->nullable();
            $table->geometry('geometry')->nullable();
            $table->integer('geometry_count')->default(0);
            $table->json('geometry_types')->nullable();
            $table->timestamps();
        });

        // Create spatial index for geometry column (PostgreSQL only)
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX layers_geometry_spatial_idx ON layers USING GIST (geometry);');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('layers');
    }
};
