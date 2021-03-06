<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePosBillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pos_bills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fiscal_year_id')->nullable();
            $table->foreign('fiscal_year_id')->references('id')->on('fiscal_years');
            //XXX customer Detail
            $table->string('customer_name')->default('Cash Account');
            $table->text('customer_address')->nullable();
            $table->text('customer_phone')->nullable();
            $table->text('customer_pan')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            //XXX Bill Detail
            $table->string('bill_no');
            $table->integer('date');
            $table->decimal('total',18,2);
            $table->decimal('discount',18,2)->default(0);
            $table->decimal('taxable',18,2);
            $table->decimal('tax',18,2);
            $table->decimal('grandtotal',18,2);
            $table->decimal('rounding',18,2)->default(0);
            $table->decimal('paid',18,2);
            $table->decimal('due',18,2)->default(0);
            $table->decimal('return',18,2)->default(0);
            //XXX Print and sync Detail
            $table->unsignedInteger('copy')->default(0);
            $table->dateTime('printed_time')->nullable();
            $table->string('printed_by')->nullable();
            $table->boolean('is_synced')->default(false);
            $table->unsignedBigInteger('sync_id')->nullable();
            $table->text('extra_info')->nullable();
            $table->unique(['fiscal_year_id','bill_no'],'unique_fiscal_bills');

            //XXX Entry Point Data
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users');

            //XXX Sales Return Data
            $table->boolean('is_canceled')->default(false);
            $table->unsignedBigInteger('ref_id')->nullable();
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
        Schema::dropIfExists('pos_bills');
    }
}
