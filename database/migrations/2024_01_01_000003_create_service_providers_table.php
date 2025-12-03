<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_providers', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('phone_number')->unique();
            $table->string('password');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('experience_years')->default(0);
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->boolean('is_verified')->default(false);
            $table->boolean('phone_verified')->default(false);
            $table->timestamps();
            
            // Index for geospatial queries
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_providers');
    }
};
