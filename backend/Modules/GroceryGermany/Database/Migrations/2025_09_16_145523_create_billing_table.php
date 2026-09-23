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
        Schema::create('billing', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id');
            $table->unsignedBigInteger('user_id');
            $table->string('invoice_number');
            $table->Integer('invoice_count')->default(0);
            $table->text('item_list');
            $table->string('total_price');
            $table->tinyInteger('payment_status')->default(0)->comment('0 = Unpaid , 1 = Paid');

            // $table->timestamps();

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));


            // Get the current database connection from .env
            $connection = env('central', 'mysql'); // Default to 'mysql' if not set

            // Get the database name for the specified connection
            $central_db_name = DB::connection($connection)->getDatabaseName();



            // Get the current database connection from .env
            $grocery_connection = env('grocery_germany', 'mysql'); // Default to 'mysql' if not set

            // Get the database name for the specified connection
            $grocery_india_db_name = DB::connection($grocery_connection)->getDatabaseName();



            $table->foreign('shop_id')
                ->references('shop_id')
                ->on("shops")
                ->onDelete('cascade');

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
        Schema::dropIfExists('billings');
    }
};
