# Laravel Cerberus

Cerberus is a flexible way to add Role-based Permissions to Laravel 5.*

## Contents

- [Installation](#installation)
- [Configuration](#configuration)
    - [User relation to roles](#user-relation-to-roles)
    - [Models](#models)
        - [Role](#role)
        - [Permission](#permission)
        - [User](#user)
        - [Soft Deleting](#soft-deleting)
- [Usage](#usage)
    - [Concepts](#concepts)
        - [Checking for Roles & Permissions](#checking-for-roles--permissions)
        - [User ability](#user-ability)
    - [Blade templates](#blade-templates)
    - [Middleware](#middleware)
- [Troubleshooting](#troubleshooting)
- [License](#license)
- [Contribution guidelines](#contribution-guidelines)
- [Additional information](#additional-information)

## Installation

1) Run this command to install Laravel 5 Cerberus: 

```
composer require michalisantoniou6/cerberus
```

Alternatively, you can add just the following to your composer.json. Then run `composer update`:

```json
"michalisantoniou6/cerberus": "2.*"
```

Require `dev-master` if you wish to always get the most recent stable version.

```json
"michalisantoniou6/cerberus": "dev-master"
```

2) Open your `config/app.php` and add the following to the `providers` array. You can skip this if you're on Laravel 5.5, as the package will register itsself.

```php
Michalisantoniou6\Cerberus\CerberusServiceProvider::class,
```

3) If you'd like to use the Facade, add the following to the `aliases` array in `config/app.php`: 

```php
'Cerberus'   => Michalisantoniou6\Cerberus\CerberusFacade::class,
```

4) Run the command below to publish the package config file `config/cerberus.php`. Set the models and keys in your config file.

```shell
php artisan vendor:publish
```

5)  If you want to use [Middleware](#middleware) (requires Laravel 5.1 or later) you also add the following:

```php
    'role' => \Michalisantoniou6\Cerberus\Middleware\CerberusRole::class,
    'permission' => \Michalisantoniou6\Cerberus\Middleware\CerberusPermission::class,
    'ability' => \Michalisantoniou6\Cerberus\Middleware\CerberusAbility::class,
```

to `routeMiddleware` array in `app/Http/Kernel.php`.

## Configuration

Set the property values in the `config/cerberus.php`.
These values will be used by cerberus to refer to the correct user/site/role/permission tables and models.

To customize table names and model namespaces, edit the `config/cerberus.php`.

### User relation to roles

Now generate the Cerberus migration:

```bash
php artisan cerberus:migration
```

It will generate the `<timestamp>_cerberus_setup_tables.php` migration.
You may add additional fields to the migration.
Review the migration, and run it with the artisan migrate command:

```bash
php artisan migrate
```

### Models

#### Role

Create a Role model inside `app/models/Role.php` using the following example:

```php
<?php namespace App;

use Michalisantoniou6\Cerberus\CerberusRole;

class Role extends CerberusRole
{
}
```

The `Role` model has three main attributes:
- `name` &mdash; Unique name for the Role, used for looking up role information in the application layer. For example: "admin", "owner", "employee".
- `display_name` &mdash; Human readable name for the Role. Not necessarily unique and optional. For example: "User Administrator", "Project Owner", "Widget  Co. Employee".
- `description` &mdash; A more detailed explanation of what the Role does. Also optional.

Both `display_name` and `description` are optional; their fields are nullable in the database.

#### Permission

Create a Permission model inside `app/models/Permission.php` using the following example:

```php
<?php namespace App;

use Michalisantoniou6\Cerberus\CerberusPermission;

class Permission extends CerberusPermission
{
}
```

The `Permission` model has the same three attributes as the `Role`:
- `name` &mdash; Unique name for the permission, used for looking up permission information in the application layer. For example: "create-post", "edit-user", "post-payment", "mailing-list-subscribe".
- `display_name` &mdash; Human readable name for the permission. Not necessarily unique and optional. For example "Create Posts", "Edit Users", "Post Payments", "Subscribe to mailing list".
- `description` &mdash; A more detailed explanation of the Permission.

In general, it may be helpful to think of the last two attributes in the form of a sentence: "The permission `display_name` allows a user to `description`."

#### User

##### For a single tenancy site, use the `CerberusUserTrait` trait in your existing `User` model. For example:

```php
<?php

use Michalisantoniou6\Cerberus\Traits\CerberusUserTrait;

class User extends Eloquent
{
    use CerberusUserTrait; // add this trait for a single tenancy site
    
    //...
}
```

This will enable the relation with `Role` and add the following methods `roles()`, `hasRole($name)`, `hasPermission($permission)`, and `ability($roles, $permissions, $options)` within your `User` model.



```bash
composer dump-autoload
```

##### If you'd like multi tenancy functionality, use `CerberusSiteUserTrait`. For example:

```php
<?php

use Michalisantoniou6\Cerberus\Traits\CerberusSiteUserTrait;

class User extends Eloquent
{
    use CerberusSiteUserTrait; //add this trait for a multi-tenant site
    
    //...
}
```

This will enable the relation with `Role` and add the following methods `roles()`, `hasRoleForSite($name, $site)`, `hasPermissionForSite($permission, $site)`, and `abilityForSite($roles, $permissions, $site, $options)` in your `User` model. You will also have to `hasRole($name)` and `hasPermission($permission)` available, in case you'd like to target all users of a certain Role.


Don't forget to dump composer autoload

**And you are ready to go.**

#### Soft Deleting

The default migration takes advantage of `onDelete('cascade')` clauses within the pivot tables to remove relations when a parent record is deleted. If for some reason you cannot use cascading deletes in your database, the CerberusRole and CerberusPermission classes, and the HasRole trait include event listeners to manually delete records in relevant pivot tables. In the interest of not accidentally deleting data, the event listeners will **not** delete pivot data if the model uses soft deleting. However, due to limitations in Laravel's event listeners, there is no way to distinguish between a call to `delete()` versus a call to `forceDelete()`. For this reason, **before you force delete a model, you must manually delete any of the relationship data** (unless your pivot tables uses cascading deletes). For example:

```php
$role = Role::findOrFail(1); // Pull back a given role

// Regular Delete
$role->delete(); // This will work no matter what

// Force Delete
$role->users()->sync([]); // Delete relationship data
$role->perms()->sync([]); // Delete relationship data

$role->forceDelete(); // Now force delete will work regardless of whether the pivot table has cascading delete
```

## Usage

### Concepts
Let's start by creating the following `Role`s and `Permission`s:

```php
$owner = new Role();
$owner->name         = 'owner';
$owner->display_name = 'Project Owner'; // optional
$owner->description  = 'User is the owner of a given project'; // optional
$owner->save();

$admin = new Role();
$admin->name         = 'admin';
$admin->display_name = 'User Administrator'; // optional
$admin->description  = 'User is allowed to manage and edit other users'; // optional
$admin->save();
```

Next, with both roles created let's assign them to the users.
Thanks to the `HasRole` trait this is as easy as:

```php
$user = User::where('username', '=', 'michele')->first();

// role attach alias
$user->attachRole($admin); // parameter can be an Role object, array, or id

// or eloquent's original technique
$user->roles()->attach($admin->id); // id only
```

Now we just need to add permissions to those Roles:

```php
$createPost = new Permission();
$createPost->name         = 'create-post';
$createPost->display_name = 'Create Posts'; // optional
// Allow a user to...
$createPost->description  = 'create new blog posts'; // optional
$createPost->save();

$editUser = new Permission();
$editUser->name         = 'edit-user';
$editUser->display_name = 'Edit Users'; // optional
// Allow a user to...
$editUser->description  = 'edit existing users'; // optional
$editUser->save();

$admin->attachPermission($createPost);
// equivalent to $admin->perms()->sync(array($createPost->id));

$owner->attachPermissions(array($createPost, $editUser));
// equivalent to $owner->perms()->sync(array($createPost->id, $editUser->id));
```

#### Checking for Roles & Permissions

Now we can check for roles and permissions simply by doing:

```php
$user->hasRole('owner');   // false
$user->hasRole('admin');   // true
$user->hasPermission('edit-user');   // false
$user->hasPermission('create-post'); // true
```

Both `hasRole()` and `can()` can receive an array of roles & permissions to check:

```php
$user->hasRole(['owner', 'admin']);       // true
$user->hasPermission(['edit-user', 'create-post']); // true
```

By default, if any of the roles or permissions are present for a user then the method will return true.
Passing `true` as a second parameter instructs the method to require **all** of the items:

```php
$user->hasRole(['owner', 'admin']);             // true
$user->hasRole(['owner', 'admin'], true);       // false, user does not have admin role
$user->hasPermission(['edit-user', 'create-post']);       // true
$user->hasPermission(['edit-user', 'create-post'], true); // false, user does not have edit-user permission
```

You can have as many `Role`s as you want for each `User` and vice versa.

The `Cerberus` class has shortcuts to both `can()` and `hasRole()` for the currently logged in user:

```php
Cerberus::hasRole('role-name');
Cerberus::hasPermission('permission-name');

// is identical to

Auth::user()->hasRole('role-name');
Auth::user()->hasPermission('permission-name');
```

You can also use placeholders (wildcards) to check any matching permission by doing:

```php
// match any admin permission
$user->hasPermission("admin.*"); // true

// match any permission about users
$user->hasPermission("*_users"); // true
```


#### User ability

More advanced checking can be done using the awesome `ability` function.
It takes in three parameters (roles, permissions, options):
- `roles` is a set of roles to check.
- `permissions` is a set of permissions to check.

Either of the roles or permissions variable can be a comma separated string or array:

```php
$user->ability(array('admin', 'owner'), array('create-post', 'edit-user'));

// or

$user->ability('admin,owner', 'create-post,edit-user');
```

This will check whether the user has any of the provided roles and permissions.
In this case it will return true since the user is an `admin` and has the `create-post` permission.

The third parameter is an options array:

```php
$options = array(
    'validate_all' => true | false (Default: false),
    'return_type'  => boolean | array | both (Default: boolean)
);
```

- `validate_all` is a boolean flag to set whether to check all the values for true, or to return true if at least one role or permission is matched.
- `return_type` specifies whether to return a boolean, array of checked values, or both in an array.

Here is an example output:

```php
$options = array(
    'validate_all' => true,
    'return_type' => 'both'
);

list($validate, $allValidations) = $user->ability(
    array('admin', 'owner'),
    array('create-post', 'edit-user'),
    $options
);

var_dump($validate);
// bool(false)

var_dump($allValidations);
// array(4) {
//     ['role'] => bool(true)
//     ['role_2'] => bool(false)
//     ['create-post'] => bool(true)
//     ['edit-user'] => bool(false)
// }

```
The `Cerberus` class has a shortcut to `ability()` for the currently logged in user:

```php
Cerberus::ability('admin,owner', 'create-post,edit-user');

// is identical to

Auth::user()->ability('admin,owner', 'create-post,edit-user');
```

### Blade templates

Three directives are available for use within your Blade templates. What you give as the directive arguments will be directly passed to the corresponding `Cerberus` function.

```php
@role('admin')
    <p>This is visible to users with the admin role. Gets translated to 
    \Cerberus::role('admin')</p>
@endrole

@permission('manage-admins')
    <p>This is visible to users with the given permissions. Gets translated to 
    \Cerberus::hasPermission('manage-admins'). The @can directive is already taken by core 
    laravel authorization package, hence the @permission directive instead.</p>
@endpermission

@ability('admin,owner', 'create-post,edit-user')
    <p>This is visible to users with the given abilities. Gets translated to 
    \Cerberus::ability('admin,owner', 'create-post,edit-user')</p>
@endability
```

Similarly, you can assume Blade directives for multi-tenancy methods.

```php
@roleforsite('admin', 15)
    <p>This is visible to users with the admin role for site with id 15. Gets translated to 
    \Cerberus::roleForSite('admin', 15)</p>
@endroleforsite

@permissionforsite('manage-admins', 15)
    <p>This is visible to users with the given permissions for site with id 15. Gets translated to 
    \Cerberus::hasPermissionForSite('manage-admins', 15). The @can directive is already taken by core 
    laravel authorization package, hence the @permission directive instead.</p>
@endpermissionforsite

@abilityforsite('admin,owner', 'create-post,edit-user', 15)
    <p>This is visible to users with the given abilities for site with id 15. Gets translated to 
    \Cerberus::abilityForSite('admin,owner', 'create-post,edit-user', 15)</p>
@endabilityforsite
``` 

### Middleware

You can use a middleware to filter routes and route groups by permission or role
```php
Route::group(['prefix' => 'admin', 'middleware' => ['role:admin']], function() {
    Route::get('/', 'AdminController@welcome');
    Route::get('/manage', ['middleware' => ['permission:manage-admins'], 'uses' => 'AdminController@manageAdmins']);
});
```

It is possible to use pipe symbol as *OR* operator:
```php
'middleware' => ['role:admin|root']
```

To emulate *AND* functionality just use multiple instances of middleware
```php
'middleware' => ['role:owner', 'role:writer']
```

For more complex situations use `ability` middleware which accepts 3 parameters: roles, permissions, validate_all
```php
'middleware' => ['ability:admin|owner,create-post|edit-user,true']
```

## Troubleshooting

If you encounter an error when doing the migration that looks like:

```
SQLSTATE[HY000]: General error: 1005 Can't create table 'laravelbootstrapstarter.#sql-42c_f8' (errno: 150)
    (SQL: alter table `role_user` add constraint role_user_user_id_foreign foreign key (`user_id`)
    references `users` (`id`)) (Bindings: array ())
```

Then it's likely that the `id` column in your user table does not match the `user_id` column in `role_user`.
Make sure both are `INT(10)`.

When trying to use the CerberusUserTrait methods, you encounter the error which looks like

    Class name must be a valid object or a string

then probably you don't have published Cerberus assets or something went wrong when you did it.
First of all check that you have the `cerberus.php` file in your `config` directory.
If you don't, then try `php artisan vendor:publish` and, if it does not appear, manually copy the `/vendor/zizaco/cerberus/src/config/config.php` file in your config directory and rename it `cerberus.php`.

## License

Cerberus is free software distributed under the terms of the MIT license.

## Contribution guidelines

Support follows PSR-1 and PSR-4 PHP coding standards, and semantic versioning.

Please report any issue you find in the issues page.  
Pull requests are welcome.

## Acknowledgment
This package was originally forked from [Zicaco/Entrust](https://github.com/Zizaco/cerberus) 
It offers the same capabilities as the original package, along with multi tenant site capabilities.

