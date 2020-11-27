<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Validation\Rule;

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
     * @return array
     * 
     * @author Gihan S <gihanshp@gmail.com>
     */
    protected static function getProps(): array
    {
        return [
            'name' => ['required', 'max:255'],
            'email' => ['required', 'email'],
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
    }

    /**
     * Get rules
     *
     * @param string $scenario Rules for the scenario
     * 
     * @return array
     * 
     * @author Gihan S <gihanshp@gmail.com>
     */
    public static function getRules(string $scenario = ''): array
    {
        $props = static::getProps();
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
}
