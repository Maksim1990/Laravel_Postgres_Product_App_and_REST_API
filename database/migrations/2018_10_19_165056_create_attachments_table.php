<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->default(0);
            $table->integer('product_id')->unsigned()->default(0);
            $table->enum('type',['video','image'])->default('image');
            $table->string('extension')->nullable();
            $table->string('path');
            $table->string('name');
            $table->text('caption')->nullable();
            $table->integer('size')->nullable();
            $table->enum('import',['Y','N'])->default('N');
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
        Schema::dropIfExists('attachments');
    }
}
