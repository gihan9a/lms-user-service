<?php

declare(strict_types=1);

namespace Tests\Integration;

use Laravel\Lumen\Testing\DatabaseMigrations;
use App\Models\User;
use Tests\TestCase;

/**
 * @covers App\Console\Kernel
 * @covers App\Exceptions\Handler
 * @covers App\Http\Controllers\Controller
 * @covers App\Http\Controllers\UserController
 * @covers App\Models\User
 * @covers App\Providers\AuthServiceProvider
 */
class UserControllerTest extends TestCase
{
    use DatabaseMigrations;

    private function getUserValidData(): array
    {
        return [
            'name' => 'User1',
            'email' => 'user1@example.com',
            'password' => 'user1pass',
            'passwordConfirm' => 'user1pass',
            'role' => 'customer',
        ];
    }

    protected function createUser(): void
    {
        $this->post('/', $this->getUserValidData());
    }

    /**
     *
     * @return void
     */
    public function testWork()
    {
        $this->get('/');
        $this->seeStatusCode(200);
        $this->assertEquals(
            $this->app->version(),
            $this->response->getContent()
        );
    }

    /**
     *
     * @return void
     */
    public function testUserCreate()
    {
        $this->createUser();
        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            'code',
            'data' => [
                'id',
                'name',
                'email',
                'role',
                'created_at',
                'updated_at',
            ],
            'errors',
        ]);
        $data = $this->getUserValidData();
        $password = $data['password'];
        unset($data['password']);
        unset($data['passwordConfirm']);
        $this->seeInDatabase('users', $data);
        $user = User::firstWhere('email', $data['email']);
        $this->assertNotNull($user);
        $this->assertNotEmpty($user->password);
        $this->assertNotEquals($password, $user->password);

        // try to create user with validation fail
        $data = $this->getUserValidData();
        // remove email and validate
        unset($data['email']);
        $this->json('POST', '/', $data)
            ->seeJson([
                'data' => null,
                'errors' => [
                    'email' => ['The email field is required.'],
                ],
                'code' => 400,
            ]);
    }

    /**
     *
     * @return void
     */
    public function testUserUpdate(): void
    {
        $this->createUser();
        $user1data1 = [
            'name' => 'User1',
            'email' => 'user1@example.com',
            'role' => 'csr',
        ];
        $this->json('PUT', '/1', $user1data1)
            ->seeJson($user1data1);

        // updating password and name
        $user1data2 = [
            'name' => 'User11',
            'password' => 'pass',
        ];
        $this->json('PUT', '/1', $user1data2)
            ->seeJson([
                'data' => null,
                'code' => 400,
                'errors' => [
                    'password' => [
                        'The password must be at least 6 characters.',
                    ],
                    'passwordConfirm' => [
                        'The password confirm field is required when password is present.',
                    ],
                ],
            ]);
        $user1data3 = [
            'name' => 'User11',
            'password' => 'password',
            'passwordConfirm' => 'password',
        ];
        $this->json('PUT', '/1', $user1data3)
            ->seeJson([
                'name' => 'User11',
            ]);
        // verify from db
        $user = User::find(1);
        $this->assertNotNull($user);
        $this->assertNotEmpty($user->password);
        $this->assertNotEquals($user1data3['password'], $user->password);

        $user2data = [
            'name' => 'User22',
        ];
        $this->json('PUT', '/2', $user2data)
            ->seeStatusCode(404)
            ->seeJson([
                'data' => null,
                'code' => 404,
                'errors' => [
                    'error' => ['Unable to find user 2'],
                ]
            ]);
    }
}
