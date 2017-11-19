<?php

namespace Michalisantoniou6\Cerberus\Contracts;


interface CerberusSiteInterface
{
    /**
     * Many-to-Many relations with the user model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users();
}