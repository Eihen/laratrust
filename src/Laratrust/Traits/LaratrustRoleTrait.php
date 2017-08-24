<?php

namespace Laratrust\Traits;

/**
 * This file is part of Laratrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Laratrust
 */

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Laratrust\Traits\LaratrustDynamicUserRelationsCalls;

trait LaratrustRoleTrait
{
    use LaratrustDynamicUserRelationsCalls;

    /**
     * Tries to return all the cached permissions of the role.
     * If it can't bring the permissions from the cache,
     * it brings them back from the DB.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function cachedPermissions()
    {
        $cacheKey = 'laratrust_permissions_for_role_' . $this->getKey();

        return Cache::remember($cacheKey, Config::get('cache.ttl', 60), function () {
            return $this->permissions()->get();
        });
    }

    /**
     * Tries to return all the cached modules of the role.
     * If it can't bring the modules from the cache,
     * it brings them back from the DB.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function cachedModules()
    {
        $cacheKey = 'laratrust_modules_for_role_' . $this->getKey();

        return Cache::remember($cacheKey, Config::get('cache.ttl', 60), function () {
            return $this->modules()->get();
        });
    }

    /**
     * Morph by Many relationship between the role and the one of the possible user models.
     *
     * @param  string  $relationship
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function getMorphByUserRelation($relationship)
    {
        return $this->morphedByMany(
            Config::get('laratrust.user_models')[$relationship],
            'user',
            Config::get('laratrust.tables.role_user'),
            Config::get('laratrust.foreign_keys.role'),
            Config::get('laratrust.foreign_keys.user')
        );
    }

    /**
     * Many-to-Many relations with the permission model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(
            Config::get('laratrust.models.permission'),
            Config::get('laratrust.tables.permission_role'),
            Config::get('laratrust.foreign_keys.role'),
            Config::get('laratrust.foreign_keys.permission')
        );
    }

    /**
     * Many-to-Many relations with the permission model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function modules()
    {
        return $this->belongsToMany(
            Config::get('laratrust.models.module'),
            Config::get('laratrust.tables.module_role'),
            Config::get('laratrust.foreign_keys.role'),
            Config::get('laratrust.foreign_keys.module')
        );
    }

    /**
     * Boots the role model and attaches event listener to
     * remove the many-to-many records when trying to delete.
     * Will NOT delete any records if the role model uses soft deletes.
     *
     * @return void|bool
     */
    public static function bootLaratrustRoleTrait()
    {
        $flushCache = function ($role) {
            $role->flushCache();
        };
        
        // If the role doesn't use SoftDeletes.
        if (method_exists(static::class, 'restored')) {
            static::restored($flushCache);
        }

        static::deleted($flushCache);
        static::saved($flushCache);

        static::deleting(function ($role) {
            if (method_exists($role, 'bootSoftDeletes') && $role->forceDeleting) {
                return;
            }

            $role->permissions()->sync([]);
            $role->modules()->sync([]);

            foreach (array_keys(Config::get('laratrust.user_models')) as $key) {
                $role->$key()->sync([]);
            }
        });
    }

    /**
     * Checks if the role has a permission by its name.
     *
     * @param  string|array  $permission       Permission name or array of permission names.
     * @param  bool  $requireAll       All permissions in the array are required.
     * @return bool
     */
    public function hasPermission($permission, $requireAll = false)
    {
        if (is_array($permission)) {
            foreach ($permission as $permissionName) {
                $hasPermission = $this->hasPermission($permissionName);

                if ($hasPermission && !$requireAll) {
                    return true;
                } elseif (!$hasPermission && $requireAll) {
                    return false;
                }
            }

            // If we've made it this far and $requireAll is FALSE, then NONE of the permissions were found.
            // If we've made it this far and $requireAll is TRUE, then ALL of the permissions were found.
            // Return the value of $requireAll.
            return $requireAll;
        }

        foreach ($this->cachedPermissions() as $perm) {
            if (str_is($permission, $perm->name)) {
                return true;
            }
        }

        foreach ($this->cachedModules() as $module) {
            foreach ($module->permssions() as $perm) {
                if (str_is($permission, $perm->name)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Save the inputted permissions.
     *
     * @param  mixed  $permissions
     * @return array
     */
    public function syncPermissions($permissions)
    {
        // If the permissions is empty it will delete all associations.
        $changes = $this->permissions()->sync($permissions);
        $this->flushCache();

        return $this;
    }

    /**
     * Attach permission to current role.
     *
     * @param  object|array  $permission
     * @return void
     */
    public function attachPermission($permission)
    {
        $this->permissions()->attach($this->getIdFor($permission));
        $this->flushCache();

        return $this;
    }

    /**
     * Detach permission from current role.
     *
     * @param  object|array  $permission
     * @return void
     */
    public function detachPermission($permission)
    {
        $this->permissions()->detach($this->getIdFor($permission));
        $this->flushCache();

        return $this;
    }

    /**
     * Attach multiple permissions to current role.
     *
     * @param  mixed  $permissions
     * @return void
     */
    public function attachPermissions($permissions)
    {
        foreach ($permissions as $permission) {
            $this->attachPermission($permission);
        }

        return $this;
    }

    /**
     * Detach multiple permissions from current role
     *
     * @param  mixed  $permissions
     * @return void
     */
    public function detachPermissions($permissions = null)
    {
        if (!$permissions) {
            $permissions = $this->permissions()->get();
        }

        foreach ($permissions as $permission) {
            $this->detachPermission($permission);
        }

        return $this;
    }

    /**
     * Checks if the role has a module by its name.
     *
     * @param  string|array $module Module name or array of module names.
     * @param  bool $requireAll All modules in the array are required.
     * @return bool
     */
    public function hasModule($module, $requireAll = false)
    {
        if (is_array($module)) {
            foreach ($module as $moduleName) {
                $hasModule = $this->hasModule($moduleName);

                if ($hasModule && !$requireAll) {
                    return true;
                } elseif (!$hasModule && $requireAll) {
                    return false;
                }
            }

            // If we've made it this far and $requireAll is FALSE, then NONE of the permissions were found.
            // If we've made it this far and $requireAll is TRUE, then ALL of the permissions were found.
            // Return the value of $requireAll.
            return $requireAll;
        }

        foreach ($this->cachedModules() as $module) {
            if (str_is($module, $module->name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Save the inputted permissions.
     *
     * @param  mixed $modules
     * @return $this
     */
    public function syncModules($modules)
    {
        // If the modules is empty it will delete all associations.
        $this->modules()->sync($modules);
        $this->flushCache();

        return $this;
    }

    /**
     * Attach permission to current role.
     *
     * @param  object|array $module
     * @return $this
     */
    public function attachModule($module)
    {
        $this->modules()->attach($this->getIdFor($module));
        $this->flushCache();

        return $this;
    }

    /**
     * Detach permission from current role.
     *
     * @param  object|array $module
     * @return $this
     */
    public function detachModule($module)
    {
        $this->modules()->detach($this->getIdFor($module));
        $this->flushCache();

        return $this;
    }

    /**
     * Attach multiple permissions to current role.
     *
     * @param  mixed $modules
     * @return $this
     */
    public function attachModules($modules)
    {
        foreach ($modules as $module) {
            $this->attachModule($module);
        }

        return $this;
    }

    /**
     * Detach multiple permissions from current role
     *
     * @param  mixed $modules
     * @return $this
     */
    public function detachModules($modules = null)
    {
        if (!$modules) {
            $modules = $this->modules()->get();
        }

        foreach ($modules as $module) {
            $this->detachModule($module);
        }

        return $this;
    }

    /**
     * Flush the role's cache.
     *
     * @return void
     */
    public function flushCache()
    {
        Cache::forget('laratrust_permissions_for_role_' . $this->getKey());
        Cache::forget('laratrust_modules_for_role_' . $this->getKey());
    }

    /**
     * Gets the it from an array, object or integer.
     *
     * @param  mixed  $permission
     * @return int
     */
    private function getIdFor($permission)
    {
        if (is_object($permission)) {
            return $permission->getKey();
        } elseif (is_numeric($permission)) {
            return $permission;
        } elseif (is_array($permission)) {
            return $permission['id'];
        }

        throw new InvalidArgumentException(
            'getIdFor function only accepts an integer, a Model object or an array with an "id" key'
        );
    }
}
