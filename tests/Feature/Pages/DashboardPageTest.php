<?php

declare(strict_types=1);

namespace Pages;

use App\Enums\LlmProvider;
use App\Models\LargeLanguageModel;
use App\Models\MoonShineUser;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DashboardPageTest extends TestCase
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
    public function mainPage(): void
    {
        // Act
        $response = $this->actingAs($this->user)->get('/');

        // Assert
        $response->assertStatus(200);
        $response->assertSee('gpt-4');
        $response->assertSee('OpenAI');
    }
}