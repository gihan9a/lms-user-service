<?php

namespace App\Models;

use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'role', 'created_at', 'updated_at',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get model props and rules
     *
     * @param User $user
     * 
     * @return array
     * 
     * @author Gihan S <gihanshp@gmail.com>
     */
    private static function getProps(User $user = null): array
    {
        $props = [
            'name' => ['required', 'max:255'],
            'email' => [
                'required',
                'email',
            ],
            'role' => [
                'required',
                Rule::in(['customer', 'csr', 'manager', 'admin']),
            ],
            'password' => ['required', 'min:6'],
            'passwordConfirm' => [
                'required_with:password',
                'same:password'
            ],
        ];

        if ($user) {
            $props['email'][] = Rule::unique('users')->ignore($user);
        } else {
            $props['email'][] = Rule::unique('users');
        }

        return $props;
    }

    /**
     * Get rules
     * 
     * @important There's something spookey with this function visibility
     * It should be public to access from external
     * But infection creates a Mutant for protected visibility.
     * Somehow laravel/lumen can access protected methods from controllers
     *
     * @param string $scenario Rules for the scenario
     * @param self   $user     A user instance to apply rules
     * 
     * @return array
     * 
     * @author Gihan S <gihanshp@gmail.com>
     */
    protected static function getRules(string $scenario = '', self $user = null): array
    {
        $props = static::getProps($user);
        switch ($scenario) {
            case 'create':
                return $props;
            case 'update':
                // remove required attribute
                return array_map(function ($key) {
                    if (($idx = array_search('required', $key)) !== false) {
                        unset($key[$idx]);
                    }
                    // reset index
                    return array_values($key);
                }, $props);
            default:
                return $props;
        }
    }

    /**
     * Get password hash of the plain text password
     *
     * @param string $password
     * 
     * @return string 
     * 
     * @author Gihan S <gihanshp@gmail.com>
     */
    protected static function getPasswordHash(string $password): string
    {
        return Hash::make($password);
    }
}
