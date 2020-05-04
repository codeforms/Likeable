<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLikesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('likes', function (Blueprint $table) 
        {
            $table->id();
            $table->morphs('likeable');
            $table->enum('response', ['like', 'dislike'])->default('like');
            $table->unsignedInteger('user_id');
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->unique(['likeable_id', 'likeable_type', 'user_id']);
        });

        Schema::create('like_counter', function (Blueprint $table) 
        {
            $table->id();
            $table->morphs('likeable');
            $table->unsignedBigInteger('like')->default(0);
            $table->unsignedBigInteger('dislike')->default(0);

            $table->unique(['likeable_id', 'likeable_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('likes');
        Schema::dropIfExists('like_counter');
    }
}
