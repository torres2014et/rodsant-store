<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_zones', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('department')->nullable();
            $table->string('city')->nullable();
            $table->decimal('cost', 12, 2)->default(0);
            $table->decimal('free_from', 12, 2)->nullable();
            $table->unsignedInteger('estimated_days')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_zones');
    }
};
