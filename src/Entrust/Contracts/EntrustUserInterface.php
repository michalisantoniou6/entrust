<?php namespace Michalisantoniou6\Entrust\Contracts;

/**
 * This file is part of Entrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Michalisantoniou6\Entrust
 */

interface EntrustUserInterface
{
    /**
     * Many-to-Many relations with Role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles();

    /**
     * Checks if the user has a role by its name.
     *
     * @param string|array $name Role name or array of role names.
     * @param bool $requireAll All roles in the array are required.
     * @param mixed $siteId
     *
     * @return bool
     */
    public function hasRole($name, $requireAll = false, $siteId);

    /**
     * Check if user has a permission by its name.
     *
     * @param string|array $permission Permission string or array of permissions.
     * @param bool         $requireAll All permissions in the array are required.
     *
     * @return bool
     */
    public function can($permission, $requireAll = false);

    /**
     * Checks role(s) and permission(s).
     *
     * @param string|array $roles       Array of roles or comma separated string
     * @param string|array $permissions Array of permissions or comma separated string.
     * @param array        $options     validate_all (true|false) or return_type (boolean|array|both)
     * @param mixed $site
     *
     * @throws \InvalidArgumentException
     *
     * @return array|bool
     */
    public function ability($roles, $permissions, $options = [], $site);

    /**
     * Alias to eloquent many-to-many relation's attach() method.
     *
     * @param mixed $role
     * @param mixed $site
     */
    public function attachRole($role, $site);

    /**
     * Alias to eloquent many-to-many relation's detach() method.
     *
     * @param mixed $role
     * @param mixed $site
     */
    public function detachRole($role, $site);

    /**
     * Attach multiple roles to a user
     *
     * @param mixed $roles
     * @param mixed $site
     */
    public function attachRoles($roles, $site);

    /**
     * Detach multiple roles from a user
     *
     * @param mixed $roles
     * @param mixed $site
     */
    public function detachRoles($roles, $site);
}
