<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginPageTest extends TestCase
{
    #[Test]
    public function loginPage()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }
}
