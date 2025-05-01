<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->default('New Area');
            $table->string('join_policy')->default('APPROVAL-NEEDED');
            $table->string('join_form')->nullable();
            $table->unsignedBigInteger('member_count')->default(0);
            $table->unsignedBigInteger('pending_member_count')->default(0);
            $table->tinyInteger('is_set_up')->default(0); 
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('areas');
    }
};
