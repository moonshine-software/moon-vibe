<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;

class PasswordResetTest extends AuthTestCase
{
    #[Test]
    public function forgotScreen(): void
    {
        $response = $this->get(route('forgot'));

        $response->assertStatus(200);
    }

    #[Test]
    public function forgotRequest(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => fake()->freeEmail(),
        ]);

        $this->post(route('forgot.reset'), ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    #[Test]
    public function forgotNotification(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => fake()->freeEmail(),
        ]);

        $this->post(route('forgot.reset'), ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification): bool {
            $response = $this->get(route('password.reset', ['token' => $notification->token]));

            $response->assertStatus(200);

            return true;
        });
    }

    #[Test]
    public function forgotSubmit(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => fake()->freeEmail(),
        ]);

        $this->post(route('forgot.reset'), ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user): bool {
            $response = $this->post(route('password.update'), [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login'));

            return true;
        });
    }
}
