<?php

declare(strict_types=1);

namespace Tests\Feature\Resources;

use App\Models\LargeLanguageModel;
use App\Models\MoonShineUser;
use App\Models\Project;
use App\Models\ProjectSchema;
use App\MoonShine\Pages\Project\ProjectFormPage;
use App\MoonShine\Resources\ProjectResource;
use App\Services\SimpleSchema;
use DevLnk\MoonShineBuilder\Services\CodeStructure\Factories\StructureFromArray;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProjectResourceTest extends TestCase
{
    private MoonShineUser $user;

    private Project $project;

    private ProjectSchema $schema;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = MoonShineUser::factory()->create();
        $llm = LargeLanguageModel::factory()->create();
        $this->project = Project::factory()->create([
            'moonshine_user_id' => $this->user->id,
            'llm_id' => $llm->id,
            'name' => 'Test Project',
            'description' => 'Test project description',
        ]);

        $this->schema = ProjectSchema::factory()->create([
            'project_id' => $this->project->id,
        ]);
    }

    public function testIndexPage()
    {
        // Act
        $response = $this->actingAs($this->user)
            ->get((string) toPage(IndexPage::class, ProjectResource::class));

        // Assert
        $response->assertStatus(200);
        $response->assertSee('Test Project');
    }

    #[Test]
    public function testFormPage(): void
    {
        // Act
        $response = $this->actingAs($this->user)
            ->get((string) toPage(ProjectFormPage::class, ProjectResource::class, [
                'resourceItem' => $this->project->id,
            ]));

        $simpleSchema = new SimpleSchema(
            new StructureFromArray(
                json_decode($this->schema->schema, true)
            )->makeStructures()
        );

        // Assert
        $response->assertStatus(200);
        $response->assertSee('Test Project');
        $response->assertSee('Test project description');
        $response->assertSee('LLM');
        $response->assertSee('Schemas');
        $response->assertSee('build-component-' . $this->project->id);
        $response->assertSee($simpleSchema->generate(), false);
    }
}
