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
        Schema::create('popups', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content')->nullable();
            $table->string('cta_text')->nullable();
            $table->string('cta_url')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamp('display_start')->nullable();
            $table->timestamp('display_end')->nullable();
            $table->json('display_pages')->nullable();
            $table->json('display_devices')->nullable();
            $table->string('frequency')->default('once_per_session');
            $table->unsignedInteger('delay_seconds')->default(0);
            $table->unsignedInteger('max_impressions')->nullable();
            $table->unsignedInteger('priority')->default(0);
            $table->json('options')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('popups');
    }
};
