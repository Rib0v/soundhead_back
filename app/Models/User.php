<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Services\PermissionService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected array $permissionsFromToken = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'address',
        'phone',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ============================ RELATIONS ============================

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function token(): HasOne
    {
        return $this->hasOne(Token::class);
    }

    public function permissionUsers(): HasMany
    {
        return $this->hasMany(PermissionUser::class);
    }

    public function permissions(): HasManyThrough
    {
        return $this->hasManyThrough(
            Permission::class,
            PermissionUser::class,
            'user_id', // foreign - permission_users
            'id',       // primary - users
            'id',       // primary - permissions
            'permission_id'  // foreign - permission_users
        );
    }

    // ====================== ACCESSORS & MUTATORS =======================

    protected function permissionIds(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->permissionUsers()->pluck('permission_id')->toArray(),
        );
    }

    // ============================= METHODS =============================

    /**
     * Загрузить реальную модель из БД
     * (при авторизации берётся
     * только id из токена)
     * 
     * @return User
     */
    public function get(): User
    {
        $this->exists = true;
        $this->refresh();

        return $this;
    }

    public function setPermissions(array $permissions): void
    {
        $this->permissionsFromToken = $permissions;
    }

    public function hasPermission(string $permission): bool
    {
        $permissionId = PermissionService::getPermissionId($permission);

        return in_array($permissionId, $this->permissionsFromToken);
    }

    public function checkPassword(string $password): bool
    {
        return Hash::check($password, $this->password);
    }
}
