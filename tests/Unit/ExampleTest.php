<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    #[Test]
    public function unitTest(): void
    {
        $user = new User();

        $this->assertEquals(['name', 'email', 'password'], $user->getFillable());
    }
}
