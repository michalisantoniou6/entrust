<?php

namespace Michalisantoniou6\Cerberus;

/**
 * This file is part of Cerberus,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 */

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Michalisantoniou6\Cerberus\Contracts\CerberusPermissionInterface;
use Michalisantoniou6\Cerberus\Traits\CerberusPermissionTrait;

class CerberusPermission extends Model implements CerberusPermissionInterface
{
    use CerberusPermissionTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table;

    /**
     * Creates a new instance of the model.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = Config::get('cerberus.permissions_table');
    }
}
