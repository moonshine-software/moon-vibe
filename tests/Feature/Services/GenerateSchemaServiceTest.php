<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Contracts\SchemaGenerateContract;
use App\Enums\SchemaStatus;
use App\Models\LargeLanguageModel;
use App\Models\MoonShineUser;
use App\Models\Project;
use App\Models\ProjectSchema;
use App\Services\GenerateSchemaService;
use App\Services\LlmProviderBuilder;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** Made using AI */
class GenerateSchemaServiceTest extends TestCase
{
    /** @var GenerateSchemaService|LegacyMockInterface */
    private GenerateSchemaService|LegacyMockInterface $service;

    /** @var LegacyMockInterface|SchemaGenerateContract */
    private SchemaGenerateContract|LegacyMockInterface $mockSchemaGenerator;

    /** @var LegacyMockInterface|LlmProviderBuilder */
    private LlmProviderBuilder|LegacyMockInterface $llmProviderBuilder;

    private string $validSchemaResult;
    
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockSchemaGenerator = Mockery::mock(SchemaGenerateContract::class);
        $this->llmProviderBuilder = Mockery::mock(LlmProviderBuilder::class);

        // Create GenerateSchemaService using app() helper
        $this->service = app(GenerateSchemaService::class, [
            'llmProviderBuilder' => $this->llmProviderBuilder,
        ]);

        $this->validSchemaResult = '{"resources": [{"name": "Stage", "column": "name", "fields": [{"type": "id", "column": "id", "methods": ["sortable()"]}, {"name": "Title", "type": "string", "column": "name", "hasFilter": true}, {"name": "Order", "type": "integer", "column": "order", "methods": ["sortable()"], "migration": {"methods": ["default(0)"]}}], "menuName": "Stages"}]}';
    }

    #[Test]
    public function generateSuccessfullyCreatesSchemaOnFirstAttempt(): void
    {
        Event::fake();

        // Arrange
        $user = MoonShineUser::factory()->create();
        $llm = LargeLanguageModel::factory()->create();
        $project = Project::factory()->create([
            'moonshine_user_id' => $user->id,
            'llm_id' => $llm->id,
        ]);
        $schema = ProjectSchema::factory()->create([
            'project_id' => $project->id,
            'status_id' => SchemaStatus::PENDING,
        ]);

        $prompt = 'Create a user management system';

        // Mock LLM provider
        $this->llmProviderBuilder
            ->shouldReceive('getProviderApi')
            ->once()
            ->with($project->llm->provider->value, $project->llm->model)
            ->andReturn($this->mockSchemaGenerator);

        // Mock schema generation
        $this->mockSchemaGenerator
            ->shouldReceive('generate')
            ->once()
            ->andReturn($this->validSchemaResult);

        // Act
        $this->service->generate($prompt, $schema->id, 3);

        // Assert
        $schema->refresh();
        $this->assertEquals(SchemaStatus::SUCCESS, $schema->status_id);
        $this->assertEquals($this->validSchemaResult, $schema->schema);
        $this->assertNull($schema->error);
    }

    #[Test]
    public function generateRetriesOnValidationFailureAndSucceedsOnSecondAttempt(): void
    {
        Event::fake();

        // Arrange
        $user = MoonShineUser::factory()->create();
        $llm = LargeLanguageModel::factory()->create();
        $project = Project::factory()->create([
            'moonshine_user_id' => $user->id,
            'llm_id' => $llm->id,
        ]);
        $schema = ProjectSchema::factory()->create([
            'project_id' => $project->id,
            'status_id' => SchemaStatus::PENDING,
        ]);

        $prompt = 'Create a user management system';
        $invalidSchemaResult = 'invalid json';

        // Mock LLM provider
        $this->llmProviderBuilder
            ->shouldReceive('getProviderApi')
            ->once()
            ->with($project->llm->provider->value, $project->llm->model)
            ->andReturn($this->mockSchemaGenerator);

        // Mock schema generation - first invalid, then valid
        $this->mockSchemaGenerator
            ->shouldReceive('generate')
            ->twice()
            ->andReturn($invalidSchemaResult, $this->validSchemaResult);

        // Act
        $this->service->generate($prompt, $schema->id, 3);

        // Assert
        $schema->refresh();
        $this->assertEquals(SchemaStatus::SUCCESS, $schema->status_id);
        $this->assertEquals($this->validSchemaResult, $schema->schema);
        $this->assertNull($schema->error);
    }

    #[Test]
    public function generateFailsAfterMaxAttemptsReached(): void
    {
        Event::fake();

        // Arrange
        $user = MoonShineUser::factory()->create();
        $llm = LargeLanguageModel::factory()->create();
        $project = Project::factory()->create([
            'moonshine_user_id' => $user->id,
            'llm_id' => $llm->id,
        ]);
        $schema = ProjectSchema::factory()->create([
            'project_id' => $project->id,
            'status_id' => SchemaStatus::PENDING,
        ]);

        $prompt = 'Create a user management system';
        $invalidSchemaResult = '[]';
        $maxTries = 2;

        // Mock LLM provider
        $this->llmProviderBuilder
            ->shouldReceive('getProviderApi')
            ->once()
            ->with($project->llm->provider->value, $project->llm->model)
            ->andReturn($this->mockSchemaGenerator);

        // Mock schema generation - always returns invalid result
        $this->mockSchemaGenerator
            ->shouldReceive('generate')
            ->once()
            ->andReturn($invalidSchemaResult);

        // Act
        $this->service->generate($prompt, $schema->id, $maxTries);

        // Assert
        $schema->refresh();
        $this->assertEquals(SchemaStatus::ERROR, $schema->status_id);
        $this->assertEquals($invalidSchemaResult, $schema->schema);
        $this->assertNotNull($schema->error);
    }

    #[Test]
    public function generateHandlesCorrectPromptFlow(): void
    {
        Event::fake();

        // Arrange
        $user = MoonShineUser::factory()->create();
        $llm = LargeLanguageModel::factory()->create();
        $project = Project::factory()->create([
            'moonshine_user_id' => $user->id,
            'llm_id' => $llm->id,
        ]);
        $schema = ProjectSchema::factory()->create([
            'project_id' => $project->id,
            'status_id' => SchemaStatus::PENDING,
            'first_prompt' => 'Original prompt',
            'schema' => '{"previous": "schema"}',
        ]);

        $correctionPrompt = 'Fix the validation errors';

        // Mock LLM provider
        $this->llmProviderBuilder
            ->shouldReceive('getProviderApi')
            ->once()
            ->with($project->llm->provider->value, $project->llm->model)
            ->andReturn($this->mockSchemaGenerator);

        // Mock schema generation
        $this->mockSchemaGenerator
            ->shouldReceive('generate')
            ->once()
            ->andReturn($this->validSchemaResult);

        // Act
        $this->service->generate($correctionPrompt, $schema->id, 3, true);

        // Assert
        $schema->refresh();
        $this->assertEquals(SchemaStatus::SUCCESS, $schema->status_id);
        $this->assertEquals($this->validSchemaResult, $schema->schema);
        $this->assertNull($schema->error);
    }

    #[Test]
    public function generateReturnsEarlyWhenSchemaNotFound(): void
    {
        Event::fake();

        // Arrange
        $nonExistentSchemaId = 999;
        $prompt = 'Create a user management system';

        // Act
        $this->service->generate($prompt, $nonExistentSchemaId, 3);

        // Assert - No exceptions should be thrown and no database changes
        $this->assertDatabaseMissing('project_schemas', ['id' => $nonExistentSchemaId]);
    }
}