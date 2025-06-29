<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\MoonShineUser;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** Made using AI */
class LoginTest extends TestCase
{
    #[Test]
    public function loginPageDisplaysCorrectly(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('Login');
        $response->assertSee('E-mail');
        $response->assertSee('Password');
    }

    #[Test]
    public function userCanLoginWithValidCredentials(): void
    {
        $user = MoonShineUser::factory()->create([
            'email' => 'test@mail.com',
            'password' => Hash::make('12345'),
        ]);

        $response = $this->post(route('authenticate'), [
            'email' => $user->email,
            'password' => '12345',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function userCannotLoginWithInvalidCredentials(): void
    {
        MoonShineUser::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post(route('authenticate'), [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
        $this->assertGuest('moonshine');
    }

    #[Test]
    public function userCannotLoginWithNonexistentEmail(): void
    {
        $response = $this->post(route('authenticate'), [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
        $this->assertGuest('moonshine');
    }

    #[Test]
    public function loginRequiresEmail(): void
    {
        $response = $this->post(route('authenticate'), [
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('moonshine');
    }

    #[Test]
    public function loginRequiresPassword(): void
    {
        $response = $this->post(route('authenticate'), [
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest('moonshine');
    }

    #[Test]
    public function authenticatedUserIsRedirectedFromLoginPage(): void
    {
        $user = MoonShineUser::factory()->create();

        $response = $this->actingAs($user, 'moonshine')
            ->get(route('login'));

        $response->assertRedirect('/');
    }
}
