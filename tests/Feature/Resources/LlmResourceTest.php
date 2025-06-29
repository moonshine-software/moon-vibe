<?php

declare(strict_types=1);

namespace Tests\Feature\Resources;

use App\Enums\LlmProvider;
use App\Models\LargeLanguageModel;
use App\Models\MoonShineUser;
use App\MoonShine\Resources\LlmResource;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LlmResourceTest extends TestCase
{
    private MoonShineUser $user;
    private LargeLanguageModel $llm;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = MoonShineUser::factory()->create();
        $this->llm = LargeLanguageModel::factory()->create([
            'provider' => LlmProvider::OPEN_AI->value,
            'model' => 'gpt-4',
            'is_default' => false,
        ]);
    }

    #[Test]
    public function indexPage(): void
    {
        // Act
        $response = $this->actingAs($this->user)
            ->get((string) toPage(IndexPage::class, LlmResource::class));

        // Assert
        $response->assertStatus(200);
        $response->assertSee('gpt-4');
        $response->assertSee('OpenAI');
    }

    #[Test]
    public function formPage(): void
    {
        // Act
        $response = $this->actingAs($this->user)
            ->get((string) toPage(FormPage::class, LlmResource::class, [
                'resourceItem' => $this->llm->id,
            ]));

        // Assert
        $response->assertStatus(200);
        $response->assertSee('gpt-4');
        $response->assertSee('Provider');
        $response->assertSee('Model');
        $response->assertSee('Default');
    }

    #[Test]
    public function createPage(): void
    {
        // Act
        $response = $this->actingAs($this->user)
            ->get((string) toPage(FormPage::class, LlmResource::class));

        // Assert
        $response->assertStatus(200);
        $response->assertSee('Provider');
        $response->assertSee('Model');
        $response->assertSee('Default');
    }
}
