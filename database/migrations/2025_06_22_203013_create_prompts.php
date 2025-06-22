<?php

use App\Models\Prompt;
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
        Schema::create('prompts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('prompt');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });

        $mainPrompt = file_get_contents(__DIR__ . '/../../.prompts/main.md');
        Prompt::query()->create([
            'title' => 'Main prompt',
            'prompt' => $mainPrompt,
            'order' => 0,
        ]);

        $todoExample = file_get_contents(__DIR__ . '/../../.prompts/todo-example.md');
        Prompt::query()->create([
            'title' => 'Todo example',
            'prompt' => $todoExample,
            'order' => 1,
        ]);

        $storeExample = file_get_contents(__DIR__ . '/../../.prompts/store-example.md');
        Prompt::query()->create([
            'title' => 'Store example',
            'prompt' => $storeExample,
            'order' => 2,
        ]);

        $leagueExample = file_get_contents(__DIR__ . '/../../.prompts/league-example.md');
        Prompt::query()->create([
            'title' => 'League example',
            'prompt' => $leagueExample,
            'order' => 3,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prompts');
    }
};
