<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mocks', function (Blueprint $table): void {
            $table->id();
            $table->json('payload');
            $table->string('slug', 16)->unique();
            $table->unsignedSmallInteger('status_code')->default(200);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mocks');
    }
};
