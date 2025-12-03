<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('provider_id')->constrained('service_providers')->onDelete('cascade');
            $table->timestamps();
            
            // Index for quick lookups when checking review eligibility
            $table->index(['user_id', 'provider_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_records');
    }
};
