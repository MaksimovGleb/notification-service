<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('external_id')->unique()->nullable();
            $table->string('channel'); // sms, email
            $table->string('recipient');
            $table->text('content');
            $table->string('status')->default('queued'); // queued, sent, delivered, failed
            $table->string('priority')->default('normal'); // normal, high
            $table->json('provider_response')->nullable();
            $table->timestamps();

            $table->index('external_id');
            $table->index('recipient');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
