<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    protected $connection = 'central';

    public function up()
    {
        Schema::create('user_email_update_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('old_email');
            $table->string('new_email');
            $table->string('token', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            // Get the current database connection from .env
            $connection = env('central', 'mysql'); // Default to 'mysql' if not set

            // Get the database name for the specified connection
            $central_db_name = DB::connection($connection)->getDatabaseName();

            // Foreign key constraint
            $table->foreign('user_id')
                ->references('user_id')
                ->on("{$central_db_name}.users")
                ->onDelete('cascade');

            $table->index(['user_id', 'new_email']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection($this->connection)->dropIfExists('user_email_update_requests');
    }
};
