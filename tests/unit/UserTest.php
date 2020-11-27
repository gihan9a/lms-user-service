<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;
use \App\Models\User;

/**
 * @covers App\Models\User
 */
class UserTest extends TestCase
{
    public function testGetRules()
    {
        $rulesAll = User::getRules();
        $this->assertIsArray($rulesAll);
        $this->assertEquals([
            'name' => 'required|max:255',
            'email' => 'required|email',
            'role' => 'required|in:customer,csr,manger,admin',
            'password' => 'required',
            'passwordConfirm' => 'required|same:password',
        ], $rulesAll);

        $rulesCreate = User::getRules('create');
        $this->assertEquals([
            'name' => 'required|max:255',
            'email' => 'required|email',
            'role' => 'required|in:customer,csr,manger,admin',
            'password' => 'required',
            'passwordConfirm' => 'required|same:password',
        ], $rulesCreate);

        $rulesUpdate = User::getRules('update');
        $this->assertEquals([
            'name' => 'required|max:255',
            'email' => 'required|email',
            'role' => 'required|in:customer,csr,manger,admin',
        ], $rulesUpdate);
    }
}
