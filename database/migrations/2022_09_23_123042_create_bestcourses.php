<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bestcourses', function (Blueprint $table) {
            $table->id();
            $table->integer('first');
            $table->integer('second');
            $table->integer('id_obmennik');
            $table->float('best_course', 10, 8);
            $table->integer('created_at')->default(time());
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bestcourses');
    }
};
