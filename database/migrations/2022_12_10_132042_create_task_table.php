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
        if (!Schema::hasTable('task')) {
            Schema::create('task', function (Blueprint $table) {
                // Settings
                $table->engine = 'InnoDB';
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';

                // Columns
                $table->id();
                $table->string('title');
                $table->date('due_date')->index();
                $table->string('ip_address');
                $table->string('user_agent');
                $table->tinyInteger('status')->default(0)->comment('0 => Pending, 1 => Completed');
                $table->unsignedBigInteger('added_by')->default(1)->comment('fk => users');
                $table->unsignedBigInteger('updated_by')->nullable()->comment('fk => users');
                $table->softDeletes();
                $table->timestamps();
            });
        }
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task');
    }
};
