<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pastes', function (Blueprint $table): void {
            $table->id();
            $table->text('content');
            $table->timestamp('expires_at')->nullable()->index();
            $table->boolean('burn_after_read')->default(false);
            $table->string('hash', 32)->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pastes');
    }
};
