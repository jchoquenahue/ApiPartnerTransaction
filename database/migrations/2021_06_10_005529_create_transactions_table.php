<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('permit_id')->nullable();
            $table->timestamps();
            $table->text('Client_id');
            $table->integer('partner_id');
            $table->bigInteger('account_id');
            $table->bigInteger('token_id');
            $table->string('transacion_type');
            $table->string('reference_number');
            $table->string('transaction_id')->nullable();
            $table->string('status')->default('PEN')->nullable();
            $table->string('request')->nullable();
            $table->string('respond')->nullable();
            $table->tinyInteger('result_code')->nullable();
            $table->string('error_code')->nullable();
            $table->datetime('datetime_end')->nullable();
            $table->string('message')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
