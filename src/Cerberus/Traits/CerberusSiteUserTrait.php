<?php

/**
 * This file is part of Cerberus,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Michalisantoniou6\Cerberus
 */

namespace Michalisantoniou6\Cerberus\Traits;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

trait CerberusSiteUserTrait
{
    /**
     * Checks role(s) and permission(s).
     *
     * @param string|array $roles Array of roles or comma separated string
     * @param string|array $permissions Array of permissions or comma separated string.
     * @param array $options validate_all (true|false) or return_type (boolean|array|both)
     *
     * @param $site
     *
     * @return array|bool
     */
    public function abilityForSite($roles, $permissions, $options = [], $site)
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
                $hasRole = $this->hasRoleForSite($roleName, false, $site);

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
            $role = $role['id'];
        }

        if ( ! is_int($role)) {
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
            $role = $role['id'];
        }

        return DB::table(Config::get('cerberus.role_user_site_table'))->where([
            [Config::get('cerberus.role_foreign_key'), '=', $role],
            [Config::get('cerberus.site_foreign_key'), '=', $site],
        ])->delete();
    }
}