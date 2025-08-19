<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_code_id')->constrained();
            $table->foreignId('user_id')->constrained(); // who logged the expense
            $table->date('expense_date');
            $table->decimal('amount', 15, 2);
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'account_code_id', 'expense_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};