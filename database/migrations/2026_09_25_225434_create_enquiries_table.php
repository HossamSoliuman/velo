<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations. The product name, SKU and URL are copied onto the enquiry
     * so it still reads correctly after the product is renamed or deleted.
     */
    public function up(): void
    {
        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name')->nullable();
            $table->string('sku')->nullable();
            $table->string('product_url', 2048)->nullable();
            $table->string('name', 100);
            $table->string('company', 150)->nullable();
            $table->string('email');
            $table->string('mobile', 20);
            $table->unsignedInteger('quantity')->nullable();
            $table->text('message')->nullable();
            $table->string('status', 20)->default('new')->index();
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enquiries');
    }
};
