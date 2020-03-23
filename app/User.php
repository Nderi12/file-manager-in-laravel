<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable,HasApiTokens;

    protected $table='cbs_auth';

    public $timestamps=false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'access_rights',
        'email',
        'password',
        'branch_id',
        'employee_id',
        'creation_date',
        'first_name',
        'last_name',
        'middle_name',
        'last_access_date',
        'role',
        'update_date',
        'uuid'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    public function roles()
    {
        return $this->belongsToMany((Role::class));
    }

    public function permissions(){
        return $this->belongsToMany(Permission::class);
    }

    /**
     * Scope a query to only include active users.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->log_id = (string) Str::uuid();
        });


            static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('status', '=', '1');
        });
    }
}
