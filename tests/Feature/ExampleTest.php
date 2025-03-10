<?php

namespace Tests\Feature;

use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    #[Test]
    public function homePage(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $user = User::query()->first();
        $this->assertNotNull($user);
        $response->assertSee($user->name);
    }
}
