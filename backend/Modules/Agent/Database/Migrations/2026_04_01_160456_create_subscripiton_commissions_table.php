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
    public function up()
    {
        Schema::create('subscription_commissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('shop_id');

            $table->string('module_type', 50);
            $table->string('commission_id', 50);
            $table->string('transaction_id', 50)->nullable();

            $table->nullableMorphs('shop_subs');

            $table->decimal('company_percentage', 5,2);
            $table->decimal('agent_percentage', 5,2);

            $table->decimal('company_amount', 10,2);
            $table->decimal('agent_amount', 10,2);

            $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid');
            $table->enum('payment_mode', ['cash', 'online'])->nullable();

            $table->timestamp('agent_payment_date')->nullable();
            $table->longText('note')->nullable();

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
        Schema::dropIfExists('subscripiton_commissions');
    }
};
