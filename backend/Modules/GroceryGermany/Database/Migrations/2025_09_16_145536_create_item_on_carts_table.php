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
        Schema::create('item_on_carts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->integer('item_id');
            $table->string('item_name');
            $table->decimal('sale_price', 10, 2);
            $table->decimal('quantity', 10, 2);
            $table->string('item_unit');
            $table->enum('location', ['sell', 'refund']);
            $table->tinyInteger('isRefund')->default(0);
            $table->timestamps();


            // Get the current database connection from .env
            $connection = env('central', 'mysql'); // Default to 'mysql' if not set

            // Get the database name for the specified connection
            $central_db_name = DB::connection($connection)->getDatabaseName();

            // dd($central_db_name);

            $table->foreign('user_id')
                ->references('user_id') // Ensure this matches the primary key of 'users'
                ->on("{$central_db_name}.users") // Correct table reference
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
        Schema::dropIfExists('item_on_carts');
    }
};
