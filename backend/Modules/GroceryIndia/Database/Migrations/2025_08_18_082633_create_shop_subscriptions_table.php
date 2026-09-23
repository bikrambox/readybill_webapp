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
        Schema::create('shop_subscriptions', function (Blueprint $table) {
            $table->id(); // Primary key for the table
            $table->unsignedBigInteger('shop_id'); // Unsigned big integer for shop_id
            $table->unsignedBigInteger('subscription_id');
            $table->json('subscription_data');

            $table->dateTime('start_date');
            $table->dateTime('end_date');
            // $table->enum('payment_status', ['free','pending', 'paid', 'failed']);
            $table->string('payment_status', 50);
            $table->enum('payment_mode', ['cash', 'online'])->nullable();
            $table->string('payment_reference', 255)->nullable();
            $table->enum('renewed_by_type', ['self', 'admin', 'agent']);
            $table->nullableMorphs('assigned_by');
            $table->longText('note')->nullable();
            $table->timestamps();


            // Get the current database connection from .env
            $connection = env('central', 'mysql'); // Default to 'mysql' if not set

            // Get the database name for the specified connection
            $central_db_name = DB::connection($connection)->getDatabaseName();

            // Get the current database connection from .env
            $grocery_connection = env('grocery_india', 'mysql'); // Default to 'mysql' if not set

            // Get the database name for the specified connection
            $grocery_india_db_name = DB::connection($grocery_connection)->getDatabaseName();


            // Add the foreign key constraint for shop_id
            $table->foreign('shop_id')
                ->references('shop_id') // Assuming 'id' is the primary key of 'shops'
                ->on("shops")
                ->onDelete('cascade'); // Cascade delete

            $table->foreign('subscription_id')
                ->references('subscription_id') 
                ->on("{$central_db_name}.subscriptions")
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
        Schema::connection($this->connection)->dropIfExists('shop_subscriptions');

    }
};
