<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\MoonShineUser;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** Made using AI */
class LogoutTest extends TestCase
{
    #[Test]
    public function authenticatedUserCanLogout(): void
    {
        $user = MoonShineUser::factory()->create();

        $response = $this->actingAs($user, 'moonshine')
            ->get(route('logout'));

        $response->assertRedirect();
        $this->assertGuest('moonshine');
    }

    #[Test]
    public function logoutInvalidatesSession(): void
    {
        $user = MoonShineUser::factory()->create();

        $this->actingAs($user, 'moonshine');

        // Store something in session
        session(['test_key' => 'test_value']);
        $this->assertEquals('test_value', session('test_key'));

        $response = $this->get(route('logout'));

        $response->assertRedirect();
        $this->assertGuest('moonshine');
        $this->assertNull(session('test_key'));
    }

    #[Test]
    public function logoutRegeneratesSessionToken(): void
    {
        $user = MoonShineUser::factory()->create();

        $this->actingAs($user, 'moonshine');
        $oldToken = session()->token();

        $response = $this->get(route('logout'));

        $response->assertRedirect();
        $this->assertNotEquals($oldToken, session()->token());
    }

    #[Test]
    public function guestUserCannotAccessLogout(): void
    {
        $response = $this->get(route('logout'));

        $response->assertRedirect('/');
    }

    #[Test]
    public function logoutRedirectsToPreviousPage(): void
    {
        $user = MoonShineUser::factory()->create();

        // Test redirect to previous page
        $response = $this->actingAs($user, 'moonshine')
            ->withHeaders(['HTTP_REFERER' => 'http://localhost/some-page'])
            ->get(route('logout'));

        $response->assertRedirect('http://localhost/some-page');
    }
}
