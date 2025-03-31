<?php

namespace Tests\Feature\Auth;

use PHPUnit\Framework\Attributes\Test;

class RegistrationTest extends AuthTestCase
{
    #[Test]
    public function registerScreen(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
    }

    #[Test]
    public function register(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Test User',
            'email' => fake()->freeEmail(),
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionDoesntHaveErrors();

        $this->assertAuthenticated();

        $response->assertRedirect(route('home'));
    }
}
