<?php

namespace App\Tests\Unit\Security;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class RoleLogicTest extends TestCase
{
    public function testAdminHasCorrectPrivileges(): void
    {
        $user = (new User())->setRole('ROLE_ADMIN');
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
    }

    public function testUserCannotAccessAdminFeatures(): void
    {
        $user = (new User())->setRole('ROLE_USER');
        $this->assertNotContains('ROLE_ADMIN', $user->getRoles());
    }
}
