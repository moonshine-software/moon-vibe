<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('builds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_schema_id')->constrained('project_schemas')->onDelete('cascade');
            $table->foreignId('moonshine_user_id')->constrained('moonshine_users')->onDelete('cascade');
            $table->unsignedTinyInteger('status_id')->default(1);
            $table->text('errors')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('builds');
    }
}; 