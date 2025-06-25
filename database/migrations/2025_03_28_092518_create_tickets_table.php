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
        Schema::create('tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('member_id')->nullable();
            $table->uuid('assessed_by')->nullable();
            $table->uuid('evaluated_by')->nullable();
            $table->uuid('status_id')->nullable();
            $table->uuid('response_id')->nullable();
            
            $table->text('stated_issue');
            $table->dateTime('raised_on');
            $table->dateTime('closed_on')->nullable();
            
            $table->foreign('member_id')->references('id')->on('members')->onDelete('set null');
            $table->foreign('assessed_by')->references('id')->on('members')->onDelete('set null');
            $table->foreign('evaluated_by')->references('id')->on('members')->onDelete('set null');
            $table->foreign('status_id')->references('id')->on('ticket_status_types')->onDelete('set null');
            $table->foreign('response_id')->references('id')->on('ticket_response_types')->onDelete('set null');
            
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
