<?php

declare(strict_types=1);

namespace Tests\Feature\Resources;

use App\Models\MoonShineUser;
use App\Models\Prompt;
use App\MoonShine\Resources\PromptResource;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PromptResourceTest extends TestCase
{
    private MoonShineUser $user;
    private Prompt $prompt;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = MoonShineUser::factory()->create();
        $this->prompt = Prompt::factory()->create([
            'title' => 'Test Prompt',
            'prompt' => 'This is a test prompt content for testing purposes.',
            'order' => 1,
        ]);
    }

    #[Test]
    public function indexPage(): void
    {
        // Act
        $response = $this->actingAs($this->user)
            ->get((string) toPage(IndexPage::class, PromptResource::class));

        // Assert
        $response->assertStatus(200);
        $response->assertSee('Test Prompt');
        $response->assertSee('Main prompt');
    }

    #[Test]
    public function formPage(): void
    {
        // Act
        $response = $this->actingAs($this->user)
            ->get((string) toPage(FormPage::class, PromptResource::class, [
                'resourceItem' => $this->prompt->id
            ]));

        // Assert
        $response->assertStatus(200);
        $response->assertSee('Test Prompt');
        $response->assertSee('This is a test prompt content');
        $response->assertSee('Title');
        $response->assertSee('Prompt');
        $response->assertSee('Order');
    }

    #[Test]
    public function createPage(): void
    {
        // Act
        $response = $this->actingAs($this->user)
            ->get((string) toPage(FormPage::class, PromptResource::class));

        // Assert
        $response->assertStatus(200);
        $response->assertSee('Title');
        $response->assertSee('Prompt');
        $response->assertSee('Order');
    }
}
