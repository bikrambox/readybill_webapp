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
        Schema::connection($this->connection)->create('categories', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('parent_id')->nullable();

            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            $table->boolean('status')->default(1);
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            $table->foreign('parent_id')
                ->references('id')
                ->on('categories')
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
        Schema::connection($this->connection)->dropIfExists('categories');
    }
};
