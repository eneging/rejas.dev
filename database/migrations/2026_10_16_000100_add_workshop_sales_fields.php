<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('plan_interest', 30)->nullable()->after('service_type');
        });

        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->decimal('annual_amount', 12, 2)->nullable()->after('monthly_amount');
        });
    }

    public function down(): void
    {
        Schema::table('leads', fn (Blueprint $table) => $table->dropColumn('plan_interest'));
        Schema::table('subscription_plans', fn (Blueprint $table) => $table->dropColumn('annual_amount'));
    }
};
