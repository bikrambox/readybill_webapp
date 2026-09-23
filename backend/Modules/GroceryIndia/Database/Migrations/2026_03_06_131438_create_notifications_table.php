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
    protected $connection = 'grocery_india';

    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {

            $table->id(); 
            $table->unsignedBigInteger('user_id');
            $table->string('type'); // 'subscription_expiry' | 'stock_alert'
            $table->string('title');
            $table->string('message');
            $table->json('data')->nullable(); // extra meta (product_id, days_left, etc.)
            $table->boolean('is_read')->default(false);
            $table->timestamps(); // Created at and updated at timestamps

            // Get the current database connection from .env
            $connection = env('central', 'mysql'); // Default to 'mysql' if not set

            // Get the database name for the specified connection
            $central_db_name = DB::connection($connection)->getDatabaseName();



            // Foreign key constraint
            $table->foreign('user_id')
                ->references('user_id')
                ->on("{$central_db_name}.users")
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection($this->connection)->dropIfExists('notifications');
    }
};
