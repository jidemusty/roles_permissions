<?php

namespace App\Traits\Permissions;

use App\{Role, Permission};

trait HasPermissionsTrait
{
    public function givePermissionTo(...$permission)
    {
        $permissions = $this->getAllPermissions(array_flatten($permission));
        
        if ($permissions === null) {
            return $this;
        }

        $this->permissions()->saveMany($permissions);

        return $this;
    }

    public function withdrawPermissionTo(...$permission)
    {
        $permissions = $this->getAllPermissions(array_flatten($permission));

        $this->permissions()->detach($permissions);

        return $this;
    }

    public function updatePermission(...$permissions)
    {
        $this->permissions()->detach();

        return $this->givePermissionTo($permissions);
    }

    protected function getAllPermissions(array $permissions)
    {
        return Permission::whereIn('name', $permissions)->get();
    }

    public function hasRole(...$roles)
    {
        foreach ($roles as $role) {
            if ($this->roles->contains('name', $role)) {
                return true;
            }

            return false;
        }
    }

    public function hasPermissionTo($permission)
    {
        return $this->hasPermissionThroughRole($permission) || $this->hasPermission($permission);
    }

    protected function hasPermission($permission)
    {
        return (bool) $this->permissions->where('name', $permission->name)->count();
    }

    protected function hasPermissionThroughRole($permission)
    {
        foreach ($permission->roles as $role) {
            if ($this->roles->contains($role)) {
                return true;
            }
        }

        return false;
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'users_roles');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'users_permissions');
    }
}