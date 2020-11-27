<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use Illuminate\Validation\Rule;
use PHPUnit\Framework\TestCase;
use \App\Models\User;

/**
 * @covers App\Models\User
 */
class UserTest extends TestCase
{
    public function testGetRules()
    {
        $rules = [
            'name' => ['required', 'max:255'],
            'email' => ['required', 'email'],
            'role' => [
                'required',
                Rule::in(['customer', 'csr', 'manager', 'admin']),
            ],
            'password' => ['required', 'min:6'],
            'passwordConfirm' => ['required_with:password', 'same:password'],
        ];
        $rulesAll = User::getRules();
        $this->assertIsArray($rulesAll);
        $this->assertEquals($rules, $rulesAll);

        $rulesCreate = User::getRules('create');
        $this->assertEquals($rules, $rulesCreate);

        $rulesUpdate = User::getRules('update');
        $this->assertEquals([
            'name' => ['max:255'],
            'email' => ['email'],
            'role' => [Rule::in(['customer', 'csr', 'manager', 'admin'])],
            'password' => ['min:6'],
            'passwordConfirm' => ['required_with:password', 'same:password'],
        ], $rulesUpdate);
    }
}
