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
        Schema::create('staff', function (Blueprint $table) {
            $table->bigIncrements('staff_id');
            $table->unsignedBigInteger('user_id'); // Foreign key column
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->text('address');
            // $table->string('shop_type');
            $table->string('photo');
            $table->unsignedBigInteger(column: 'addedBy');

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));

            // Get the current database connection from .env
            $central_connection = env('central', 'mysql'); // Default to 'mysql' if not set

            // Get the database name for the specified connection
            $central_db_name = DB::connection($central_connection)->getDatabaseName();


            // Get the current database connection from .env
            $grocery_connection = env('grocery_india', 'mysql'); // Default to 'mysql' if not set

            // Get the database name for the specified connection
            $grocery_india_db_name = DB::connection($grocery_connection)->getDatabaseName();


            // Foreign key constraint
            $table->foreign('user_id')
                ->references('user_id')
                ->on("{$central_db_name}.users")
                ->onDelete('cascade'); // Cascade delete

            // Foreign key constraint
            $table->foreign('addedBy')
                ->references('shop_id')
                ->on("shops")
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
        Schema::connection($this->connection)->dropIfExists('staff');
    }
};
