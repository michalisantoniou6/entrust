<?php namespace Michalisantoniou6\Cerberus;

/**
 * This file is part of Cerberus,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Michalisantoniou6\Cerberus
 */

use Michalisantoniou6\Cerberus\Contracts\CerberusSiteInterface;
use Michalisantoniou6\Cerberus\Traits\CerberusSiteTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class CerberusSite extends Model implements CerberusSiteInterface
{
    use CerberusSiteTrait;

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
        $this->table = Config::get('cerberus.sites_table');
    }

}
