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
        Schema::create('customers', function (Blueprint $table) {
            $table->bigIncrements('customer_id');
            $table->string('dial_code', 5)->nullable()->default('91');
            $table->string('mobile',15)->unique();
            $table->string('name',100)->nullable();
            $table->longText('address')->nullable();
            $table->string('state', 50)->nullable();
            $table->string('state_code', 10)->nullable();
            $table->string('gstin', 50)->nullable();

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
        Schema::dropIfExists('customers');
    }
};
