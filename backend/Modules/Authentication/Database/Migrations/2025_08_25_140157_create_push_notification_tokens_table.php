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
        Schema::create('push_notification_tokens', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('user_id'); // Reference to the user
            $table->string('device_token'); // Notification message
            $table->timestamp('read_at')->nullable(); // Marks when the notification is read
            $table->tinyInteger('status')->default(0);
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
        Schema::dropIfExists('push_notification_tokens');
    }
};
