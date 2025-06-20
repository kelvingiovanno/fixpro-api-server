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
        Schema::create('maintainers', function (Blueprint $table) {
            $table->uuid('ticket_issue_id');
            $table->uuid('member_id');

            $table->foreign('ticket_issue_id')->references('id')->on('ticket_issues')->onDelete('set null');
            $table->foreign('member_id')->references('id')->on('members')->onDelete('set null');
            
            $table->primary(['ticket_issue_id', 'member_id']);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_maintenance_staffs');
    }
};
