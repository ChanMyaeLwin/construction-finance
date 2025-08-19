<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('account_codes', function (Blueprint $table) {
            $table->foreignId('account_code_type_id')
                  ->nullable()
                  ->constrained('account_code_types')
                  ->nullOnDelete()
                  ->after('id');
        });
    }
    public function down(): void {
        Schema::table('account_codes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('account_code_type_id');
        });
    }
};