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
        Schema::create('work_order_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('resource_type');
            $table->string('resource_name');
            $table->string('resource_size');
            $table->string('previewable_on')->unique();
            
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_order_documents');
    }
};
