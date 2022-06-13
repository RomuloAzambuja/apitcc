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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('avatar')->default('default.png');
            $table->string('email')->unique();
            $table->string('password');
        });
        Schema::create('userfavorites', function (Blueprint $table) {
            $table->id();
            $table->integer('id_user');
            $table->integer('id_society');
        });
        Schema::create('userappointments', function (Blueprint $table) {
            $table->id();
            $table->integer('id_user');
            $table->integer('id_society');
            $table->dateTime('ap_datetime');
        });

        Schema::create('society', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('avatar')->default('default.png');
            $table->float('stars')->default(0);
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
        });
        Schema::create('societyphotos', function (Blueprint $table) {
            $table->id();
            $table->integer('id_society');
            $table->string('url');
        });
        Schema::create('societyreviews', function (Blueprint $table) {
            $table->id();
            $table->integer('id_society');
            $table->float('rate');
        });
        Schema::create('societyservices', function (Blueprint $table) {
            $table->id();
            $table->integer('id_society');
            $table->string('name');
            $table->float('price');
        });
        Schema::create('societytestimonials', function (Blueprint $table) {
            $table->id();
            $table->integer('id_society');
            $table->string('name');
            $table->float('rate');
            $table->string('body');
        });
        Schema::create('societyavailability', function (Blueprint $table) {
            $table->id();
            $table->integer('id_society');
            $table->integer('weekday');
            $table->text('hours');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('userfavorites');
        Schema::dropIfExists('userappointments');
        Schema::dropIfExists('society');
        Schema::dropIfExists('societysphotos');
        Schema::dropIfExists('societyreviews');
        Schema::dropIfExists('societyservices');
        Schema::dropIfExists('societytestimonials');
        Schema::dropIfExists('societyavailability');
    }
};
