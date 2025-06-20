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
        Schema::create('capabilities', function (Blueprint $table) {
            $table->uuid('member_id');
            $table->uuid('capability_id');

            $table->foreign('member_id')->references('id')->on('members')->onDelete('set null');
            $table->foreign('capability_id')->references('id')->on('member_capabilities')->onDelete('set null');
            $table->primary(['member_id','capability_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capabilities');
    }
};
