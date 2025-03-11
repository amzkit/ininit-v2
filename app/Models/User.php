<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
//use Laravel\Fortify\TwoFactorAuthenticatable;
//use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

use Vinkla\Hashids\Facades\Hashids;


class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    //use HasProfilePhoto;
    //use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'access_token',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'store',
    ];

    public function providers()
    {
        return $this->hasMany(Provider::class, 'user_id', 'id');
    }

    public function getRoleAttribute()
    {
        if($this->store){
            if($this->store->user_id === $this->id){
                return 'owner';
            }
        }
        return 'admin';
    }

    public function getStoreAttribute()
    {
        // Simply get first store (Main Store)
        //return $this->asOne(Store::class);
        $user_id = $this->id;
        $store = null;

        // Get default store from adminstore relationship table
        //dd($user_id);
        $default_adminstore = AdminStore::where([
            ['user_id', '=', $user_id],
            ['default', '=', true],
        ])->first();

        if(!isset($default_adminstore)){
            return null;
        }

        $store = Store::find($default_adminstore->store_id);


        //$store = Store::();
        return $store;
    }

    public function stores()
    {
        // Stores which this user own (can be several)
        return $this->hasMany(Store::class);
    }

    public function adminstores()
    {
        // Store which being admin (relationship)
        return $this->hasMany(AdminStore::class);
    }

    public function getAdminCode()
    {
        $own_store = Store::where('user_id', $this->id)->first();
        if(isset($own_store)){
            $own_store_id = $own_store->id;
            $own_store_admin_count = AdminStore::where('store_id', $own_store->id)->count();
            return Hashids::connection('adminstore')->encode($own_store_id, $own_store_admin_count);
        }else{
            return null;
        }



    }

    public function getAdmins()
    {
        $own_store = Store::where('user_id', $this->id)->first();
        if(!isset($own_store)){
            return null;
        }

        $own_store_id = $own_store->id;
        $adminstores = AdminStore::where('store_id', $own_store_id)->get();

        $admin_ids = array_column($adminstores->toArray(), 'user_id');
        $admins = User::whereIn('id', $admin_ids)->get();

        return $admins;

    }

    public function telegramChats()
    {
        return $this->hasMany(TelegramChat::class);
    }

}
