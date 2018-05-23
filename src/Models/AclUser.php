<?php

namespace Laracl\Models;

use Illuminate\Database\Eloquent\Model;

class AclUser extends \App\User
{
    public function getFillableColumns()
    {
        return $this->fillable;
    }

    //
    // Relacionamentos
    //

    public function group()
    {
        return $this->hasOne('Laracl\Models\AclGroup', 'id', 'group_id');
    }

    public function permissions()
    {
        return $this->hasMany('Laracl\Models\AclUserPermissions', 'user_id', 'id');
    }
}
