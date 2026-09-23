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
    protected $connection = 'grocery_germany';
    public function up()
    {
        Schema::create('report_generations', function (Blueprint $table) {
            $table->id();
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('shop_id');
            $table->enum('environment', ['local', 'development', 'staging', 'production'])->default('local');
            $table->json('parameters'); // JSON for date filters (date_from, date_to)
            $table->tinyInteger('status')->default(0); // 0: requested, 1: processing, 2: ready
            $table->enum('report_type', ['pdf', 'excel', 'csv']);
            $table->tinyInteger('retry_count')->default(0);
            $table->string('file_path')->nullable(); // Storage path for the generated report
            $table->timestamps();

            // Get the current database connection from .env
            $connection = env('central', 'mysql'); // Default to 'mysql' if not set

            // Get the database name for the specified connection
            $central_db_name = DB::connection($connection)->getDatabaseName();


            // Foreign keys
            $table->foreign('user_id')
                ->references('user_id')
                ->on("{$central_db_name}.users")
                ->onDelete('cascade');


            $table->foreign('shop_id')
                ->references('shop_id')
                ->on('shops')
                ->onDelete('cascade');
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
        Schema::dropIfExists('report_generations');
    }
};
