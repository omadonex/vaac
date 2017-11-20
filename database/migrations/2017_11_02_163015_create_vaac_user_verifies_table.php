<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVaacUserVerifiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vaac_user_verifies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('method')->index();
            $table->unsignedInteger('user_id')->index();
            $table->string('value')->nullable();
            $table->string('token')->index();
            $table->timestamp('used_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vaac_user_verifies');
    }
}
