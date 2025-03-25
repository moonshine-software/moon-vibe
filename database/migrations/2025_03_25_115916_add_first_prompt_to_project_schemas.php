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
        Schema::table('project_schemas', function (Blueprint $table) {
            $table->text('first_prompt')->nullable()->after('schema');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_schemas', function (Blueprint $table) {
            $table->dropColumn('first_prompt');
        });
    }
};
