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
        Schema::create('preferences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unsigned();
            $table->smallInteger('preference_mrp')->default(0);
            $table->smallInteger('preference_mrp_invoice')->default(0);
            $table->smallInteger('preference_quantity')->default(0);
            $table->smallInteger('preference_hsn')->default(0);
            $table->smallInteger('preference_hsn_invoice')->default(0);
            $table->smallInteger('preference_invoice_format')->default(0);

            $table->smallInteger('preference_transaction_mark_as_paid')->default(0);

            $table->smallInteger('preference_barcode')->default(0);
            $table->smallInteger('preference_invoice_gst_complaint')->default(0);

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));

            // Get the current database connection from .env
            $connection = env('central', 'mysql'); // Default to 'mysql' if not set

            // Get the database name for the specified connection
            $central_db_name = DB::connection($connection)->getDatabaseName();

            
            $table->foreign('user_id')
                ->references('user_id')
                ->on("{$central_db_name}.users")  // Specify the database name before the table name
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
        Schema::dropIfExists('preferences');
    }
};
