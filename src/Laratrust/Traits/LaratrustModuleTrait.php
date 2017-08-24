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

trait LaratrustModuleTrait
{
    use LaratrustDynamicUserRelationsCalls;

    /**
     * Tries to return all the cached permissions of the module.
     * If it can't bring the permissions from the cache,
     * it brings them back from the DB.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function cachedPermissions()
    {
        $cacheKey = 'laratrust_permissions_for_module_' . $this->getKey();

        return Cache::remember($cacheKey, Config::get('cache.ttl', 60), function ()
        {
            return $this->permissions()->get();
        });
    }

    /**
     * Morph by Many relationship between the module and one of the possible user models.
     *
     * @param  string $relationship
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function getMorphByUserRelation($relationship)
    {
        return $this->morphedByMany(
            Config::get('laratrust.user_models')[$relationship],
            'user',
            Config::get('laratrust.tables.module_user'),
            Config::get('laratrust.foreign_keys.module'),
            Config::get('laratrust.foreign_keys.user')
        );
    }

    /**
     * Many-to-Many relations with the role model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(
            Config::get('laratrust.models.role'),
            Config::get('laratrust.tables.module_role'),
            Config::get('laratrust.foreign_keys.module'),
            Config::get('laratrust.foreign_keys.role')
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
            Config::get('laratrust.tables.permission_module'),
            Config::get('laratrust.foreign_keys.module'),
            Config::get('laratrust.foreign_keys.permission')
        );
    }

    /**
     * Boots the module model and attaches event listener to
     * remove the many-to-many records when trying to delete.
     * Will NOT delete any records if the module model uses soft deletes.
     *
     * @return void|bool
     */
    public static function bootLaratrustModuleTrait()
    {
        $flushCache = function ($module)
        {
            $module->flushCache();
        };

        // If the module doesn't use SoftDeletes.
        if (method_exists(static::class, 'restored')) {
            static::restored($flushCache);
        }

        static::deleted($flushCache);
        static::saved($flushCache);

        static::deleting(function ($module)
        {
            if (method_exists($module, 'bootSoftDeletes') && $module->forceDeleting) {
                return;
            }

            $module->permissions()->sync([]);

            foreach (array_keys(Config::get('laratrust.user_models')) as $key) {
                $module->$key()->sync([]);
            }
        });
    }

    /**
     * Checks if the module has a permission by its name.
     *
     * @param  string|array $permission Permission name or array of permission names.
     * @param  bool $requireAll All permissions in the array are required.
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

        return false;
    }

    /**
     * Save the inputted permissions.
     *
     * @param  mixed $permissions
     * @return $this
     */
    public function syncPermissions($permissions)
    {
        // If the permissions is empty it will delete all associations.
        $this->permissions()->sync($permissions);
        $this->flushCache();

        return $this;
    }

    /**
     * Attach permission to current module.
     *
     * @param  object|array $permission
     * @return $this
     */
    public function attachPermission($permission)
    {
        $this->permissions()->attach($this->getIdFor($permission));
        $this->flushCache();

        return $this;
    }

    /**
     * Detach permission from current module.
     *
     * @param  object|array $permission
     * @return $this
     */
    public function detachPermission($permission)
    {
        $this->permissions()->detach($this->getIdFor($permission));
        $this->flushCache();

        return $this;
    }

    /**
     * Attach multiple permissions to current module.
     *
     * @param  mixed $permissions
     * @return $this
     */
    public function attachPermissions($permissions)
    {
        foreach ($permissions as $permission) {
            $this->attachPermission($permission);
        }

        return $this;
    }

    /**
     * Detach multiple permissions from current module
     *
     * @param  mixed $permissions
     * @return $this
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
     * Flush the module's cache.
     *
     * @return void
     */
    public function flushCache()
    {
        Cache::forget('laratrust_permissions_for_module_' . $this->getKey());
    }

    /**
     * Gets the it from an array, object or integer.
     *
     * @param  mixed $permission
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
