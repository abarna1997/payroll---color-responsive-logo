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
        Schema::table('payroll_periods', function (Blueprint $table) {
            $table->unsignedBigInteger('processed_by')->nullable()->after('status');
            $table->timestamp('processed_at')->nullable()->after('processed_by');
            
            $table->unsignedBigInteger('reviewed_by')->nullable()->after('processed_at');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->text('review_comment')->nullable()->after('reviewed_at');
            
            $table->unsignedBigInteger('approved_by')->nullable()->after('review_comment');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('approval_comment')->nullable()->after('approved_at');
            
            $table->unsignedBigInteger('locked_by')->nullable()->after('approval_comment');
            $table->timestamp('locked_at')->nullable()->after('locked_by');
            $table->text('lock_reason')->nullable()->after('locked_at');
            
            $table->unsignedBigInteger('parent_period_id')->nullable()->after('lock_reason');
            $table->integer('revision_number')->default(0)->after('parent_period_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_periods', function (Blueprint $table) {
            $table->dropColumn([
                'processed_by', 'processed_at',
                'reviewed_by', 'reviewed_at', 'review_comment',
                'approved_by', 'approved_at', 'approval_comment',
                'locked_by', 'locked_at', 'lock_reason',
                'parent_period_id', 'revision_number'
            ]);
        });
    }
};
