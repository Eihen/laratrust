<?php

namespace Laratrust\Contracts;

/**
 * This file is part of Laratrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Laratrust
 */

interface LaratrustRoleInterface
{
    /**
     * Morph by Many relationship between the role and the one of the possible user models.
     *
     * @param  string  $relationship
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function getMorphByUserRelation($relationship);

    /**
     * Many-to-Many relations with the permission model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions();

    /**
     * Many-to-Many relations with the Module model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function modules();

    /**
     * Checks if the role has a permission by its name.
     *
     * @param  string|array  $permission       Permission name or array of permission names.
     * @param  bool  $requireAll       All permissions in the array are required.
     * @return bool
     */
    public function hasPermission($permission, $requireAll);

    /**
     * Save the inputted permissions.
     *
     * @param  mixed  $permissions
     * @return array
     */
    public function syncPermissions($permissions);

    /**
     * Attach permission to current role.
     *
     * @param  object|array  $permission
     * @return void
     */
    public function attachPermission($permission);

    /**
     * Detach permission from current role.
     *
     * @param  object|array  $permission
     * @return void
     */
    public function detachPermission($permission);

    /**
     * Attach multiple permissions to current role.
     *
     * @param  mixed  $permissions
     * @return void
     */
    public function attachPermissions($permissions);

    /**
     * Detach multiple permissions from current role
     *
     * @param  mixed  $permissions
     * @return void
     */
    public function detachPermissions($permissions);

    /**
     * Checks if the role has a module by its name.
     *
     * @param  string|array $module Module group name or array of module names.
     * @param  bool $requireAll All modules in the array are required.
     *
     * @return bool
     */
    public function hasModule($module, $requireAll);

    /**
     * Save the inputted modules.
     *
     * @param $modules
     *
     * @return array
     */
    public function syncModules($modules);

    /**
     * Attach module to current role.
     *
     * @param  object|array $module
     *
     * @return void
     */
    public function attachModule($module);

    /**
     * Detach permission from current role.
     *
     * @param  object|array $module
     *
     * @return void
     */
    public function detachModule($module);

    /**
     * Attach multiple permissions to current role.
     *
     * @param  mixed $modules
     *
     * @return void
     */
    public function attachModules($modules);

    /**
     * Detach multiple permissions from current role
     *
     * @param  mixed $modules
     *
     * @return void
     */
    public function detachModules($modules);
}
