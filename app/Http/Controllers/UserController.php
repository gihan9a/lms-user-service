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
            'password' => Hash::make($request->get('password')),
        ]);

        return $this->respond(User::create($request->all()));
    }

    public function update($id, Request $request): \Illuminate\Http\JsonResponse
    {
        $this->validate($request, User::getRules('update'));

        $user = $this->findModelOrFail(User::class, $id);
        
        // hash password
        if (($password = $request->get('password', false)) !== false) {
            $request->merge([
                'password' => Hash::make($password),
            ]);
        }
        $user->update($request->all());

        return $this->respond($user);
    }
}
