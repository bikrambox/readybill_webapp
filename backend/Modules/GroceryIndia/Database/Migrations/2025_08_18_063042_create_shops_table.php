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
        Schema::create('shops', function (Blueprint $table) {
            $table->bigIncrements('shop_id');
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            // $table->string('mobile')->unique();
            $table->string('email')->nullable();
            $table->string('business_name');
            $table->text('address');
            // $table->string('shop_type');
            $table->string('gstin');
            $table->string('logo');
            $table->string('signature')->nullable();

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));

            // Get the current database connection from .env
            $connection = env('central', 'mysql'); // Default to 'mysql' if not set

            // Get the database name for the specified connection
            $central_db_name = DB::connection($connection)->getDatabaseName();


            $table->foreign('user_id')
                ->references('user_id')
                ->on("{$central_db_name}.users")
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
        Schema::connection($this->connection)->dropIfExists('shops');
    }
};
