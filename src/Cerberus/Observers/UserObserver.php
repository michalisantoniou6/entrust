<?php

namespace Michalisantoniou6\Cerberus\Observers;


use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class UserObserver
{
    /**
     * Listen to the restoring event.
     *
     * @param $user
     *
     * @return void
     */
    public function restoring($user)
    {
        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(Config::get('cerberus.role_user_site_table'))->flush();
        }
    }

    /**
     * Listen to the saving event.
     *
     * @param $user
     *
     * @return void
     */
    public function saving($user)
    {
        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(Config::get('cerberus.role_user_site_table'))->flush();
        }
    }

    /**
     * Listen to the deleting event.
     *
     * @param  $user
     *
     * @return void
     */
    public function deleting($user)
    {
        if ( ! method_exists(Config::get('auth.model'), 'bootSoftDeletes')) {
            $user->roles()->sync([]);
        }

        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(Config::get('cerberus.role_user_site_table'))->flush();
        }
    }
}
