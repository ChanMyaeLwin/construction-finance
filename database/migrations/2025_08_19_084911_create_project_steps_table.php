<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('project_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('step_no');                // order
            $table->string('name');                                 // step name
            $table->boolean('is_done')->default(false);             // completion
            $table->timestamps();

            $table->unique(['project_id','step_no']);               // no duplicate step_no per project
        });
    }
    public function down(): void {
        Schema::dropIfExists('project_steps');
    }
};