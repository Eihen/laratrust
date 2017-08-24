<?php

namespace Laratrust\Contracts;

/**
 * This file is part of Laratrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Laratrust
 */

interface LaratrustUserInterface
{
    /**
     * Many-to-Many relations with Role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function roles();

    /**
     * Many-to-Many relations with Permission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function permissions();

    /**
     * Many-to-Many relations with the Module model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function modules();

    /**
     * Checks if the user has a role by its name.
     *
     * @param  string|array  $name       Role name or array of role names.
     * @param  string|bool   $team      Team name or requiredAll roles.
     * @param  bool          $requireAll All roles in the array are required.
     * @return bool
     */
    public function hasRole($name, $team = null, $requireAll = false);

    /**
     * Check if user has a permission by its name.
     *
     * @param  string|array  $permission Permission string or array of permissions.
     * @param  string|bool  $team      Team name or requiredAll roles.
     * @param  bool  $requireAll All roles in the array are required.
     * @return bool
     */
    public function hasPermission($permission, $team = null, $requireAll = false);

    /**
     * Check if user has a permission by its name.
     *
     * @param  string|array  $permission Permission string or array of permissions.
     * @param  string|bool  $team      Team name or requiredAll roles.
     * @param  bool  $requireAll All permissions in the array are required.
     * @return bool
     */
    public function can($permission, $team = null, $requireAll = false);

    /**
     * Check if user has a permission by its name.
     *
     * @param  string|array  $permission  Permission string or array of permissions.
     * @param  string|bool  $team  Team name or requiredAll roles.
     * @param  bool  $requireAll  All permissions in the array are required.
     * @return bool
     */
    public function isAbleTo($permission, $team = null, $requireAll = false);

    /**
     * Checks role(s) and permission(s).
     *
     * @param  string|array  $roles       Array of roles or comma separated string
     * @param  string|array  $permissions Array of permissions or comma separated string.
     * @param  string|bool  $team      Team name or requiredAll roles.
     * @param  array  $options     validate_all (true|false) or return_type (boolean|array|both)
     * @throws \InvalidArgumentException
     * @return array|bool
     */
    public function ability($roles, $permissions, $team = null, $options = []);

    /**
     * Alias to eloquent many-to-many relation's attach() method.
     *
     * @param  mixed  $role
     * @param  mixed  $team
     * @return static
     */
    public function attachRole($role, $team = null);

    /**
     * Alias to eloquent many-to-many relation's detach() method.
     *
     * @param  mixed  $role
     * @param  mixed  $team
     * @return static
     */
    public function detachRole($role, $team = null);

    /**
     * Attach multiple roles to a user.
     *
     * @param  mixed  $roles
     * @param  mixed  $team
     * @return static
     */
    public function attachRoles($roles = [], $team = null);

    /**
     * Detach multiple roles from a user.
     *
     * @param  mixed  $roles
     * @param  mixed  $team
     * @return static
     */
    public function detachRoles($roles = [], $team = null);

    /**
     * Sync roles to the user.
     *
     * @param  array  $roles
     * @param  mixed  $team
     * @return static
     */
    public function syncRoles($roles = [], $team = null);

    /**
     * Alias to eloquent many-to-many relation's attach() method.
     *
     * @param  mixed  $permission
     * @param  mixed  $team
     * @return static
     */
    public function attachPermission($permission, $team = null);

    /**
     * Alias to eloquent many-to-many relation's detach() method.
     *
     * @param  mixed  $permission
     * @param  mixed  $team
     * @return static
     */
    public function detachPermission($permission, $team = null);

    /**
     * Attach multiple permissions to a user.
     *
     * @param  mixed  $permissions
     * @param  mixed  $team
     * @return static
     */
    public function attachPermissions($permissions = [], $team = null);

    /**
     * Detach multiple permissions from a user.
     *
     * @param  mixed  $permissions
     * @param  mixed  $team
     * @return static
     */
    public function detachPermissions($permissions = [], $team = null);

    /**
     * Sync roles to the user.
     *
     * @param  array  $permissions
     * @return static
     */
    public function syncPermissions($permissions = [], $team = null);

    /**
     * Check if user has a module by its name.
     *
     * @param  string|array $module Module string or array of permissions.
     * @param  string|bool $team Team name or requiredAll roles.
     * @param  bool $requireAll All roles in the array are required.
     * @return bool
     */
    public function hasModule($module, $team = null, $requireAll = false);

    /**
     * Save the inputted modules.
     *
     * @param $modules
     *
     * @return array
     */
    public function syncModules($modules);

    /**
     * Attach module to current user.
     *
     * @param  object|array $module
     *
     * @return void
     */
    public function attachModule($module);

    /**
     * Detach permission from current user.
     *
     * @param  object|array $module
     *
     * @return void
     */
    public function detachModule($module);

    /**
     * Attach multiple permissions to current user.
     *
     * @param  mixed $modules
     *
     * @return void
     */
    public function attachModules($modules);

    /**
     * Detach multiple permissions from current user
     *
     * @param  mixed $modules
     *
     * @return void
     */
    public function detachModules($modules);

    /**
     * Checks if the user owns the thing.
     *
     * @param  Object  $thing
     * @param  string  $foreignKeyName
     * @return boolean
     */
    public function owns($thing);

    /**
     * Checks if the user has some role and if he owns the thing.
     *
     * @param  string|array  $role
     * @param  Object  $thing
     * @param  array  $options
     * @return boolean
     */
    public function hasRoleAndOwns($role, $thing, $options = []);

    /**
     * Checks if the user can do something and if he owns the thing.
     *
     * @param  string|array  $permission
     * @param  Object  $thing
     * @param  array  $options
     * @return boolean
     */
    public function canAndOwns($permission, $thing, $options = []);

    /**
     * Checks if the user has some module and if he owns the thing.
     *
     * @param string|array $module
     * @param Object $thing
     * @param array $options
     *
     * @return boolean
     */
    public function hasModuleAndOwns($module, $thing, $options = []);

    /**
     * Return all the user permissions.
     *
     * @return boolean
     */
    public function allPermissions();
}
