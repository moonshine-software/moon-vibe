<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use PHPUnit\Framework\Attributes\Test;

class AuthenticationTest extends AuthTestCase
{
    #[Test]
    public function loginScreen(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
    }

    #[Test]
    public function authenticate(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('authenticate'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionDoesntHaveErrors();

        $this->assertAuthenticated();

        $response->assertRedirect(route('profile', absolute: false));
    }

    #[Test]
    public function invalidPassword(): void
    {
        $user = User::factory()->create();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    #[Test]
    public function logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('logout'));

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
