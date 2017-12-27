<?php

/**
 * This file is part of Cerberus,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Michalisantoniou6\Cerberus
 */

namespace Michalisantoniou6\Cerberus\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;

trait CerberusSiteUserTrait
{
    /**
     * Check if user has a permission by its name.
     *
     * @param string|array $permission Permission string or array of permissions.
     * @param bool $requireAll All permissions in the array are required.
     *
     * @return bool
     */
    public function hasPermission($permission, $requireAll = false)
    {
        if (is_array($permission)) {
            foreach ($permission as $permName) {
                $hasPerm = $this->hasPermission($permName);

                if ($hasPerm && ! $requireAll) {
                    return true;
                } elseif ( ! $hasPerm && $requireAll) {
                    return false;
                }
            }

            // If we've made it this far and $requireAll is FALSE, then NONE of the perms were found
            // If we've made it this far and $requireAll is TRUE, then ALL of the perms were found.
            // Return the value of $requireAll;
            return $requireAll;
        } else {
            foreach ($this->cachedRoles() as $role) {
                // Validate against the Permission table
                foreach ($role->cachedPermissions() as $perm) {
                    if (str_is($permission, $perm->name)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Check if user has a permission by its name.
     *
     * @param string|array $permission Permission string or array of permissions.
     * @param bool $requireAll All permissions in the array are required.
     *
     * @return bool
     */
    public function hasPermissionForSite($permission, $site, $requireAll = false)
    {
        if (is_a(Model::class, $site)) {
            $site = $site->getKey();
        }

        if (is_array($permission)) {
            foreach ($permission as $permName) {
                $hasPerm = $this->can($permName);

                if ($hasPerm && ! $requireAll) {
                    return true;
                } elseif ( ! $hasPerm && $requireAll) {
                    return false;
                }
            }

            // If we've made it this far and $requireAll is FALSE, then NONE of the perms were found
            // If we've made it this far and $requireAll is TRUE, then ALL of the perms were found.
            // Return the value of $requireAll;
            return $requireAll;
        } else {
            foreach ($this->cachedRoles() as $role) {
                if ($role->pivot->{Config::get('cerberus.site_foreign_key')} != $site) {
                    continue;
                }
                // Validate against the Permission table
                foreach ($role->cachedPermissions() as $perm) {
                    if (str_is($permission, $perm->name)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function cachedRoles()
    {
        $userPrimaryKey = $this->primaryKey;
        $cacheKey       = 'cerberus_roles_for_user_' . $this->$userPrimaryKey;
        if (Cache::getStore() instanceof TaggableStore) {
            return Cache::tags(Config::get('cerberus.role_user_site_table'))->remember($cacheKey,
                Config::get('cache.ttl'), function () {
                    return $this->roles()->get();
                });
        } else {
            return $this->roles()->get();
        }
    }

    /**
     * Checks if the user has a role by its name.
     *
     * @param string|array $name Role name or array of role names.
     * @param bool $requireAll All roles in the array are required.
     *
     * @return bool
     */
    public function hasRole($name, $requireAll = false)
    {
        if (is_array($name)) {
            foreach ($name as $roleName) {
                $hasRole = $this->hasRole($roleName, false);

                if ($hasRole && ! $requireAll) {
                    return true;
                } elseif ( ! $hasRole && $requireAll) {
                    return false;
                }
            }

            // If we've made it this far and $requireAll is FALSE, then NONE of the roles were found
            // If we've made it this far and $requireAll is TRUE, then ALL of the roles were found.
            // Return the value of $requireAll;
            return $requireAll;
        } else {
            foreach ($this->cachedRoles() as $role) {
                if ($role->name == $name) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Many-to-Many relations with Role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Config::get('cerberus.role'), Config::get('cerberus.role_user_site_table'),
            Config::get('cerberus.user_foreign_key'), Config::get('cerberus.role_foreign_key'))
                    ->withPivot(Config::get('cerberus.site_foreign_key'));
    }

    /**
     * Checks whether $site is required and $site is empty.
     *
     * @param $site
     *
     * @return bool
     * @throws \Exception
     */
    public function validateSite($site)
    {
        if ( ! $site) {
            throw new \Exception("The site is required.");
        }

        return true;
    }

    /**
     * Checks role(s) and permission(s).
     *
     * @param string|array $roles Array of roles or comma separated string
     * @param string|array $permissions Array of permissions or comma separated string.
     * @param $site
     * @param array $options validate_all (true|false) or return_type (boolean|array|both)
     *
     * @return array|bool
     */
    public function abilityForSite($roles, $permissions, $site, $options = [])
    {
        $this->validateSite($site);

        // Convert string to array if that's what is passed in.
        if ( ! is_array($roles)) {
            $roles = explode(',', $roles);
        }
        if ( ! is_array($permissions)) {
            $permissions = explode(',', $permissions);
        }

        // Set up default values and validate options.
        if ( ! isset($options['validate_all'])) {
            $options['validate_all'] = false;
        } else {
            if ($options['validate_all'] !== true && $options['validate_all'] !== false) {
                throw new InvalidArgumentException();
            }
        }
        if ( ! isset($options['return_type'])) {
            $options['return_type'] = 'boolean';
        } else {
            if ($options['return_type'] != 'boolean' &&
                $options['return_type'] != 'array' &&
                $options['return_type'] != 'both') {
                throw new InvalidArgumentException();
            }
        }

        // Loop through roles and permissions and check each.
        $checkedRoles       = [];
        $checkedPermissions = [];
        foreach ($roles as $role) {
            $checkedRoles[$role] = $this->hasRoleForSite($role, false, $site);
        }
        foreach ($permissions as $permission) {
            $checkedPermissions[$permission] = $this->can($permission);
        }

        // If validate all and there is a false in either
        // Check that if validate all, then there should not be any false.
        // Check that if not validate all, there must be at least one true.
        if (($options['validate_all'] && ! (in_array(false, $checkedRoles) || in_array(false, $checkedPermissions))) ||
            ( ! $options['validate_all'] && (in_array(true, $checkedRoles) || in_array(true, $checkedPermissions)))) {
            $validateAll = true;
        } else {
            $validateAll = false;
        }

        // Return based on option
        if ($options['return_type'] == 'boolean') {
            return $validateAll;
        } elseif ($options['return_type'] == 'array') {
            return ['roles' => $checkedRoles, 'permissions' => $checkedPermissions];
        } else {
            return [$validateAll, ['roles' => $checkedRoles, 'permissions' => $checkedPermissions]];
        }

    }

    /**
     * Checks if the user has a role by its name for a site.
     *
     * @param string|array $name Role name or array of role names.
     * @param bool $requireAll All roles in the array are required.
     * @param $site
     *
     * @return bool
     */
    public function hasRoleForSite($name, $site, $requireAll = false)
    {
        if (is_array($name)) {
            foreach ($name as $roleName) {
                $hasRole = $this->hasRoleForSite($roleName, $site, $requireAll);

                if ($hasRole && ! $requireAll) {
                    return true;
                } elseif ( ! $hasRole && $requireAll) {
                    return false;
                }
            }

            // If we've made it this far and $requireAll is FALSE, then NONE of the roles were found
            // If we've made it this far and $requireAll is TRUE, then ALL of the roles were found.
            // Return the value of $requireAll;
            return $requireAll;
        } else {
            foreach ($this->cachedRoles() as $role) {
                if ($role->name == $name && ($role->pivot->{Config::get('cerberus.site_foreign_key')} == $site)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Attach multiple roles to a user
     *
     * @param mixed $roles
     * @param $site
     */
    public function attachRolesForSite($roles, $site)
    {
        $this->validateSite($site);

        foreach ($roles as $role) {
            $this->attachRoleForSite($role, $site);
        }
    }

    /**
     * Alias to eloquent many-to-many relation's attach() method.
     *
     * @param mixed $role
     * @param mixed $site
     */
    public function attachRoleForSite($role, $site)
    {
        $this->validateSite($site);

        if (is_object($role)) {
            $role = $role->getKey();
        }

        if (is_array($role)) {
            foreach ($role as $key => $roleId) {
                $this->attachRoleForSite($roleId, $site);
                unset($role[$key]);
            }
        }

        if ( ! is_numeric($role)) {
            throw new \Exception("Not a valid role id.");
        }

        $this->roles()->attach($role, [
            Config::get('cerberus.site_foreign_key') => $site,
        ]);
    }

    /**
     * Detach multiple roles from a user
     *
     * @param mixed $roles
     * @param $site
     */
    public function detachRolesForSite($roles = null, $site)
    {
        $this->validateSite($site);

        if ( ! $roles) {
            $roles = $this->roles()->where(Config::get('cerberus.site_foreign_key'), '=', $site)->get();
        }

        foreach ($roles as $role) {
            $this->detachRoleForSite($role, $site);
        }
    }

    /**
     * Alias to eloquent many-to-many relation's detach() method.
     *
     * @param mixed $role
     * @param $site
     *
     * @return
     */
    public function detachRoleForSite($role, $site)
    {
        $this->validateSite($site);

        if (is_object($role)) {
            $role = $role->getKey();
        }

        if (is_array($role)) {
            foreach ($role as $key => $roleId) {
                $this->detachRoleForSite($roleId, $site);
                unset($role[$key]);
            }
        }

        return DB::table(Config::get('cerberus.role_user_site_table'))->where([
            [Config::get('cerberus.role_foreign_key'), '=', $role],
            [Config::get('cerberus.site_foreign_key'), '=', $site],
            [Config::get('cerberus.user_foreign_key'), '=', $this->getKey()],
        ])->delete();
    }
}
