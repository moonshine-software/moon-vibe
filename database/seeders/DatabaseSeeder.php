<?php

namespace Database\Seeders;

use App\Enums\LlmProvider;
use App\Models\LargeLanguageModel;
use App\Models\Project;
use App\Models\ProjectSchema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use MoonShine\Laravel\Models\MoonshineUser;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        MoonshineUser::query()->create([
            'id' => 1,
            'name' => 'Ivan',
            'email' => 'ivan@mail.ru',
            'moonshine_user_role_id' => 1,
            'password' => Hash::make(12345),
            'settings' => '{"build": {"repository": "https://github.com/dev-lnk/moonshine-blank.git"}, "generation": {"attempts": 5}}'
        ]);

        $llm = LargeLanguageModel::query()->create([
            'provider' => LlmProvider::OPEN_AI->value,
            'model' => 'gpt-4.1-mini',
            'is_default' => 1,
        ]);

        $project = Project::query()->create([
            'name' => 'Test',
            'description' => 'make todo app with comments',
            'llm_id' => $llm->id,
            'moonshine_user_id' => 1,
        ]);

        $schema = '{"resources": [{"name": "Task", "column": "title", "fields": [{"type": "id", "column": "id", "methods": ["sortable()"]}, {"name": "Title", "type": "string", "column": "title", "hasFilter": true}, {"name": "Description", "type": "longText", "field": "Textarea", "column": "content"}, {"name": "Priority", "type": "tinyInteger", "field": "Select", "column": "priority", "default": 1, "methods": ["options([1 => \'Low\', 2 => \'Medium\', 3 => \'High\'])"]}, {"name": "Deadline", "type": "dateTime", "column": "deadline"}, {"name": "Assignee", "type": "BelongsTo", "column": "moonshine_user_id", "methods": ["nullable()"], "nullable": true, "relation": {"table": "moonshine_users"}, "required": false, "model_class": "\\\\MoonShine\\\\Laravel\\\\Models\\\\MoonshineUser"}, {"name": "Tags", "type": "string", "column": "tags"}, {"name": "Comments", "type": "HasMany", "column": "comments", "methods": ["creatable()"], "relation": {"table": "comments", "foreign_key": "task_id"}}], "menuName": "Tasks", "timestamps": true, "soft_deletes": true}, {"name": "Comment", "column": "comment", "fields": [{"type": "id", "column": "id", "methods": ["sortable()"]}, {"name": "Comment", "type": "string", "column": "comment"}, {"name": "Task", "type": "BelongsTo", "column": "task_id", "methods": ["nullable()"], "nullable": true, "relation": {"table": "tasks"}, "required": false}, {"name": "User", "type": "BelongsTo", "column": "moonshine_user_id", "methods": ["nullable()"], "nullable": true, "relation": {"table": "moonshine_users"}, "required": false, "model_class": "\\\\MoonShine\\\\Laravel\\\\Models\\\\MoonshineUser"}], "menuName": "Comments", "timestamps": true}]}';

        ProjectSchema::query()->create([
            'project_id' => $project->id,
            'schema' => $schema,
            'first_prompt' => 'make todo app with comments',
            'status_id' => 2,
        ]);
    }
}
