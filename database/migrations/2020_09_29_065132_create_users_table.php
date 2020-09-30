<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            // 字段
            $table->increments('id');
            $table->string('account', 128)->nullable()->comment('用户账号');
            $table->string('password', 128)->nullable()->comment('用户密码');
            $table->string('nickname', 128)->nullable()->comment('昵称');
            $table->string('headimgurl', 512)->nullable()->comment('头像');
            $table->tinyInteger('sex')->default(0)->comment('性别（1:男  2:女  3:未知）');
            $table->string('city', 128)->nullable()->comment('城市');
            $table->string('province', 128)->nullable()->comment('省份');
            $table->string('country', 128)->nullable()->comment('国家');
            $table->string('official_account_openid', 128)->nullable()->comment('公众号openid')->unique();
            $table->string('mini_program_openid', 128)->nullable()->comment('小程序openid')->unique();
            $table->string('unionid', 128)->nullable()->comment('公众号和小程序主体 id')->unique();
            $table->integer('created_at')->nullable();
            $table->integer('updated_at')->nullable();
            $table->integer('deleted_at')->nullable();
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
    }
}
