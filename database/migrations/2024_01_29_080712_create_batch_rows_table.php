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
        Schema::create('batch_rows', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('batch_id');
            $table->string('status');
            $table->longText('content');
            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
            $table->longText('errors')->nullable();
            $table->timestamp('processed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_rows');
    }
};
