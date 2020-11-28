<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function create(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->validate($request, User::getRules('create'));

        $request->merge([
            'password' => User::getPasswordHash($request->input('password')),
        ]);

        return $this->respond(User::create($request->all()));
    }

    public function update(int $id, Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $this->findModelOrFail(User::class, $id);

        $this->validate($request, User::getRules('update', $user));

        // hash password
        if (($password = $request->input('password')) !== null) {
            $request->merge([
                'password' => User::getPasswordHash($password),
            ]);
        }
        $user->update($request->all());

        return $this->respond($user);
    }
}
