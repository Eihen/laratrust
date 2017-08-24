<?php

namespace Laratrust\Contracts;

/**
 * This file is part of Laratrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Laratrust
 */

interface LaratrustModuleInterface
{
    /**
     * Morph by Many relationship between the module and one of the possible user models.
     *
     * @param  string $relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function getMorphByUserRelation($relationship);

    /**
     * Many-to-Many relations with the role model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles();

    /**
     * Many-to-Many relations with the permission model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions();

    /**
     * Checks if the module has a permission by its name.
     *
     * @param  string|array $permission Permission name or array of permission names.
     * @param  bool $requireAll All permissions in the array are required.
     *
     * @return bool
     */
    public function hasPermission($permission, $requireAll);

    /**
     * Save the inputted permissions.
     *
     * @param  mixed $permissions
     *
     * @return array
     */
    public function syncPermissions($permissions);

    /**
     * Attach permission to current module.
     *
     * @param   object|array $permission
     * @return  void
     */
    public function attachPermission($permission);

    /**
     * Detach permission from current module.
     *
     * @param  object|array $permission
     * @return void
     */
    public function detachPermission($permission);

    /**
     * Attach multiple permissions to current module.
     *
     * @param  mixed $permissions
     * @return void
     */
    public function attachPermissions($permissions);

    /**
     * Detach multiple permissions from current module
     *
     * @param  mixed $permissions
     * @return void
     */
    public function detachPermissions($permissions);
}
