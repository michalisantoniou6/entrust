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
use Michalisantoniou6\Cerberus\Contracts\CerberusRoleInterface;
use Michalisantoniou6\Cerberus\Traits\CerberusRoleTrait;

class CerberusRole extends Model implements CerberusRoleInterface
{
    use CerberusRoleTrait;

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
        $this->table = Config::get('cerberus.roles_table');
    }
}
