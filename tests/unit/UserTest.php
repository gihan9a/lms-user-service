<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Tests\TestCase;

/**
 * @covers App\Models\User
 * @covers App\Providers\AuthServiceProvider
 */
class UserTest extends TestCase
{
    public function testGetRules(): void
    {
        $rules = [
            'name' => ['required', 'max:255'],
            'email' => [
                'required',
                'email',
                Rule::unique('users'),
            ],
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
            'email' => ['email', Rule::unique('users')],
            'role' => [Rule::in(['customer', 'csr', 'manager', 'admin'])],
            'password' => ['min:6'],
            'passwordConfirm' => ['required_with:password', 'same:password'],
        ], $rulesUpdate);
    }

    public function testGetPasswordHash(): void
    {
        $str = 'password';
        $hash = User::getPasswordHash($str);
        $this->assertNotEquals($str, $hash);
    }
}
