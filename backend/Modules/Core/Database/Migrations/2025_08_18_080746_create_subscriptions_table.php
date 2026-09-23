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
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id('subscription_id');
            $table->string('plan_name', 255);
            $table->string('heading',150)->nullable();
            $table->string('subheading',150)->nullable();
            $table->float('price');
            $table->integer('price',10);
            $table->longText('description')->nullable();
            $table->tinyInteger('active')->default(0);
            $table->boolean('is_best_value')->default(0);
            $table->string('shop_type', 50);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
};
