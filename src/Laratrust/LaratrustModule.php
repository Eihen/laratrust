<?php

namespace Laratrust;

/**
 * This file is part of Laratrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Laratrust
 */

use Laratrust\Contracts\LaratrustModuleInterface;
use Laratrust\Traits\LaratrustModuleTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class LaratrustModule extends Model implements LaratrustModuleInterface
{
    use LaratrustModuleTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table;

    /**
     * Creates a new instance of the model.
     *
     * @param  array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = Config::get('laratrust.tables.modules');
    }
}
