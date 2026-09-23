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
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('user_id');
            $table->string('mobile')->unique();
            $table->string('email',100)->nullable();
            $table->string('password');
            $table->string('ip_address');
            $table->tinyInteger('isAdmin')->default(0);
            $table->tinyInteger('isAgent')->default(0);
            $table->longText('token')->nullable();
            $table->timestamp('current_logged_in')->useCurrent();
            $table->timestamp('last_logged_in');
            $table->tinyInteger('active')->default(1);
            $table->tinyInteger('isVerified')->default(0);
            $table->string('shop_type', 50);
            $table->string('module_type', 50);
            $table->json('country_details')->nullable();
            $table->string('country_code', 5)->nullable();
            $table->string('detected_country_code', 5)->nullable();
            $table->string('lang', 5)->nullable();
            $table->string('email_verification_token')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
