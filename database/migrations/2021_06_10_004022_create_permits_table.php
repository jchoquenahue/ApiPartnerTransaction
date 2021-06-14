<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permits', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('client_id');
            $table->bigInteger('account_id')->index();
            $table->dateTime('expire_at');
            $table->dateTime('respond_at')->nullable();
            $table->dateTime('notified_at')->nullable();
            $table->Biginteger('partner_id');
            $table->Biginteger('dni_id')->unsigned()->index();
            $table->string('grant_type')->nullable();
            $table->text('token',2000)->nullable();
            $table->text('token_refresh')->nullable();
            $table->dateTime('token_expire_at')->nullable();
            $table->string('reference_number');
            $table->string('status')->default('PEN');
            $table->text('error')->nullable();
            $table->tinyInteger('result_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permits');
    }
}
