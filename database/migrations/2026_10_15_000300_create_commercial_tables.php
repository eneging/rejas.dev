<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('client_id')->constrained();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('scope')->nullable();
            $table->text('conditions')->nullable();
            $table->string('currency', 3)->default('PEN');
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('estimated_cost', 12, 2)->nullable(); // dato interno de margen
            $table->unsignedSmallInteger('delivery_days')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->string('status')->default('borrador'); // borrador, enviada, aceptada, rechazada, vencida
            $table->date('valid_until')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('sent_via')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->string('acceptance_channel')->nullable();
            $table->text('acceptance_evidence')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('quote_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total', 12, 2);
            $table->unsignedSmallInteger('position')->default(0);
        });

        Schema::create('quote_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->json('snapshot');
            $table->string('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('client_id')->constrained();
            $table->foreignId('quote_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('borrador'); // borrador, enviado, firmado, anulado
            $table->date('signed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('contract_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('original_name');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_files');
        Schema::dropIfExists('contracts');
        Schema::dropIfExists('quote_versions');
        Schema::dropIfExists('quote_items');
        Schema::dropIfExists('quotes');
    }
};
