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
    public function up()
    {
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Foreign key column
            $table->string('key')->unique();  // UUID key
            $table->boolean('status')->default(true);
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->onDelete('cascade'); // Cascade delete

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('api_keys');
    }
};
