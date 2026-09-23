<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    protected $connection = 'central';
    public function up()
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('mobile')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('ip_address')->nullable();

            $table->longText('token')->nullable();
            $table->timestamp('current_logged_in')->useCurrent();
            $table->timestamp('last_logged_in')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('admins');
        Schema::connection($this->connection)->dropIfExists('admins');
    }
};
