<?php namespace Michalisantoniou6\Entrust;

/**
 * This file is part of Entrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Michalisantoniou6\Entrust
 */

use Michalisantoniou6\Entrust\Contracts\EntrustRoleInterface;
use Michalisantoniou6\Entrust\Traits\EntrustRoleTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class EntrustRole extends Model implements EntrustRoleInterface
{
    use EntrustRoleTrait;

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
        $this->table = Config::get('entrust.roles_table');
    }

}
