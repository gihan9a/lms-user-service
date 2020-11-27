<?php

declare(strict_types=1);

use Laravel\Lumen\Testing\DatabaseMigrations;

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

    protected function createUser(): void
    {
        $this->post('/', [
            'name' => 'User1',
            'email' => 'user1@example.com',
            'password' => 'user1pass',
            'passwordConfirm' => 'user1pass',
            'role' => 'customer',
        ]);
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
