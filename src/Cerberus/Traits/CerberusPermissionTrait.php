<?php namespace Michalisantoniou6\Cerberus\Traits;

/**
 * This file is part of Cerberus,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Michalisantoniou6\Cerberus
 */

use Illuminate\Support\Facades\Config;

trait CerberusPermissionTrait
{

    public function roles()
    {
        return $this->morphedByMany(Config::get('cerberus.role'),'permissible', Config::get('cerberus.permissibles_table'), Config::get('cerberus.permission_foreign_key'), 'permissible_id')
                    ->withPivot(['is_active'])
                    ->withTimestamps();
    }
    
    public function users()
    {
        return $this->morphedByMany(Config::get('cerberus.user'),'permissible', Config::get('cerberus.permissibles_table'))
                    ->withPivot(['is_active'])
                    ->withTimestamps();
    }

    /**
     * Boot the permission model
     * Attach event listener to remove the many-to-many records when trying to delete
     * Will NOT delete any records if the permission model uses soft deletes.
     *
     * @return void|bool
     */
    public static function boot()
    {
        parent::boot();

        static::deleting(function($permission) {
            if (!method_exists(Config::get('cerberus.permission'), 'bootSoftDeletes')) {
                $permission->roles()->sync([]);
            }

            return true;
        });
    }
}
