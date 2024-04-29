<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PermissionUser extends Model
{
    use HasFactory;

    public function permission(): HasOne
    {
        return $this->hasOne(Permission::class, 'id');
    }
}
