<?php

declare(strict_types=1);

namespace Tests\Feature\Pages;

use App\Models\MoonShineUser;
use App\MoonShine\Pages\SettingsPage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SettingsPageTest extends TestCase
{
    private MoonShineUser $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = MoonShineUser::factory()->create();
    }

    #[Test]
    public function settingsPage(): void
    {
        // Act
        $response = $this->actingAs($this->user)->get((string) toPage(SettingsPage::class));

        // Assert
        $response->assertStatus(200);
    }
}
