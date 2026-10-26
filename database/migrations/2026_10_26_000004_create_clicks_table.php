<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clicks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('url_id')->constrained()->cascadeOnDelete();
            $table->string('browser', 32)->nullable()->index();
            $table->string('country_code', 2)->nullable()->index();
            $table->string('referer', 2048)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->timestamp('created_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clicks');
    }
};
