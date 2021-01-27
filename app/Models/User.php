<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends \TCG\Voyager\Models\User
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'active',
        'phone',
        'user_name',
        'user_surname',
        'address',
        'cashback_id',
        'Token',
        'AccountId',
        'cashback'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getUserByPhone($phone)
    {
        if( ! $phone ) return false;
        $user = $this->where("phone", $phone)->first();
        if( $user ) {
            return $user;
        }

        return false;
    }

    public function currentUser()
    {
        return Auth::user();
    }

    public function userCheck()
    {
        if(Auth::check())
            return true;

        return false;
    }

    public function isAuthorized()
    {
        if($this->userCheck())
            return true;

        return false;
    }

    public function login($user)
    {
        if($user instanceof Authenticatable) 
            Auth::login($user, true);
    }

    public function logout()
    {
        request()->session()->put('register_phone', '');
        request()->session()->put('register_address', '');
        request()->session()->put('register_appartment', '');
        request()->session()->put('register_confirm', '');
        request()->session()->put('register_payment_status', '');
        request()->session()->put('register_tarrif_id', '');
        request()->session()->put('register_cart', '');
        request()->session()->put('register_email', '');
        request()->session()->put('register_user_id', '');
        request()->session()->put('register_password', '');
        request()->session()->put('register_polis_id', '');
        request()->session()->put('register_cash_id', '');
        request()->session()->put('register_another_polic', '');
        return Auth::logout();
    }
}
