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
        Schema::create('agent_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->text('address');
            $table->string('pan_number');
            $table->string('photo');
            $table->string('aadhar_card');
            $table->string('qr_code');
            // $table->tinyInteger('isVerified')->default(0);
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

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('agent_details');
        Schema::connection($this->connection)->dropIfExists('agent_details');
    }
};
