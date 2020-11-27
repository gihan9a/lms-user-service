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
     * Validation rules
     * 
     * @var array
     * 
     * @author Gihan S <gihanshp@gmail.com>
     */
    static array $rules = [
        'name' => 'required|max:255',
        'email' => 'required|email',
        'role' => 'required|in:customer,csr,manger,admin',
        'password' => 'required',
        'passwordConfirm' => 'required|same:password',
    ];

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
        switch ($scenario) {
            case 'create':
                return static::$rules;
            case 'update':
                $rules = static::$rules;
                unset($rules['password']);
                unset($rules['passwordConfirm']);
                return $rules;
            default:
                return static::$rules;
        }
    }
}
