<?php

namespace Michalisantoniou6\Cerberus\Traits;


use Illuminate\Support\Facades\Config;

trait CerberusSiteTrait
{
    /**
     * Many-to-Many relations with Role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(Config::get('cerberus.user'), Config::get('cerberus.role_user_site_table'),
            Config::get('cerberus.site_foreign_key'), Config::get('cerberus.user_foreign_key'));
    }

}