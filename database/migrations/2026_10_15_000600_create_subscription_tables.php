<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('monthly_amount', 12, 2);
            $table->string('currency', 3)->default('PEN');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained();
            $table->foreignId('subscription_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('service');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('PEN');
            $table->string('periodicity')->default('mensual');
            $table->date('starts_at');
            $table->date('next_due_date');
            $table->string('status')->default('activa'); // activa, suspendida, cancelada
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('subscription_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->date('period_start');
            $table->date('due_date');
            $table->decimal('amount', 12, 2);
            $table->string('status')->default('por_cobrar'); // por_cobrar, cobrado, anulado
            $table->timestamps();
            $table->unique(['subscription_id', 'period_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_charges');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('subscription_plans');
    }
};
