<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebMonksPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('webmonks_posts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger("user_id")->index()->nullable();

            $table->dateTime("posted_at")->index()->nullable()->comment("Public posted at time, if this is in future then it wont appear yet");
            $table->boolean("is_published")->default(true);

            $table->timestamps();
        });

        Schema::create('webmonks_post_categories', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger("post_id")->index();
            $table->foreign('post_id')->references('id')->on('webmonks_posts')->onDelete("cascade");

            $table->unsignedInteger("category_id")->index();
            $table->foreign('category_id')->references('id')->on('webmonks_categories')->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('webmonks_posts');
    }
}
