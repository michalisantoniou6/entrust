<?php

/**
 * This file is part of Cerberus,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Michalisantoniou6\Cerberus
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Cerberus Role Model
    |--------------------------------------------------------------------------
    |
    | This is the Role model used by Cerberus to create correct relations.  Update
    | the role if it is in a different namespace.
    |
    */
    'role'                   => 'App\Role',

    /*
    |--------------------------------------------------------------------------
    | Cerberus Roles Table
    |--------------------------------------------------------------------------
    |
    | This is the roles table used by Cerberus to save roles to the database.
    |
    */
    'roles_table'            => 'roles',

    /*
    |--------------------------------------------------------------------------
    | Cerberus role foreign key
    |--------------------------------------------------------------------------
    |
    | This is the role foreign key used by Cerberus to make a proper
    | relation between permissions and roles & roles and users
    |
    */
    'role_foreign_key'       => 'role_id',

    /*
    |--------------------------------------------------------------------------
    | Site role foreign key
    |--------------------------------------------------------------------------
    |
    | This is the site foreign key used by Cerberus to make a proper
    | relation between permissions and roles, roles and users and users and sites
    |
    */
    'site_foreign_key'       => 'site_id',

    /*
    |--------------------------------------------------------------------------
    | Application User Model
    |--------------------------------------------------------------------------
    |
    | This is the User model used by Cerberus to create correct relations.
    | Update the User if it is in a different namespace.
    |
    */
    'user'                   => 'App\User',

    /*
    |--------------------------------------------------------------------------
    | Cerberus role_user Table
    |--------------------------------------------------------------------------
    |
    | This is the role_user table used by Cerberus to save assigned roles to the
    | database.
    |
    */
    'role_user_site_table'   => 'role_user_site',

    /*
    |--------------------------------------------------------------------------
    | Cerberus user foreign key
    |--------------------------------------------------------------------------
    |
    | This is the user foreign key used by Cerberus to make a proper
    | relation between roles and users
    |
    */
    'user_foreign_key'       => 'user_id',

    /*
    |--------------------------------------------------------------------------
    | Cerberus Permission Model
    |--------------------------------------------------------------------------
    |
    | This is the Permission model used by Cerberus to create correct relations.
    | Update the permission if it is in a different namespace.
    |
    */
    'permission'             => 'App\Permission',

    /*
    |--------------------------------------------------------------------------
    | Cerberus Permissions Table
    |--------------------------------------------------------------------------
    |
    | This is the permissions table used by Cerberus to save permissions to the
    | database.
    |
    */
    'permissions_table'      => 'permissions',

    /*
    |--------------------------------------------------------------------------
    | Cerberus permission_role_user Table
    |--------------------------------------------------------------------------
    |
    | This is the permission_role table used by Cerberus to save relationship
    | between permissions and roles/users to the database.
    |
    */
    'permissibles_table'  => 'permissibles',

    /*
    |--------------------------------------------------------------------------
    | Cerberus permission foreign key
    |--------------------------------------------------------------------------
    |
    | This is the permission foreign key used by Cerberus to make a proper
    | relation between permissions and roles
    |
    */
    'permission_foreign_key' => 'permission_id',

    /*
    |--------------------------------------------------------------------------
    | Sites Model
    |--------------------------------------------------------------------------
    |
    | This is the Sites model used by Cerberus to create correct relations between roles, users and sites.
    | Update the permission if it is in a different namespace.
    |
    */
    'site'                   => 'App\Site',

    /*
    |--------------------------------------------------------------------------
    | Cerberus Sites Table
    |--------------------------------------------------------------------------
    |
    | This is the permissions table used by Cerberus to save permissions to the
    | database.
    |
    */
    'sites_table'            => 'sites',
];
