<?php namespace Michalisantoniou6\Entrust\Middleware;

/**
 * This file is part of Entrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Michalisantoniou6\Entrust
 */

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Config;

class EntrustAbility
{
	const DELIMITER = '|';

	protected $auth;

	/**
	 * Creates a new instance of the middleware.
	 *
	 * @param Guard $auth
	 */
	public function __construct(Guard $auth)
	{
		$this->auth = $auth;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param Closure $next
	 * @param $roles
	 * @param $permissions
	 * @param bool $validateAll
     * @param mixed $site
	 * @return mixed
	 */
	public function handle($request, Closure $next, $roles, $permissions, $validateAll = false, $site)
	{
		if (!is_array($roles)) {
			$roles = explode(self::DELIMITER, $roles);
		}

		if (!is_array($permissions)) {
			$permissions = explode(self::DELIMITER, $permissions);
		}

		if (!is_bool($validateAll)) {
			$validateAll = filter_var($validateAll, FILTER_VALIDATE_BOOLEAN);
		}

		if (is_a($site, Config::get('entrust.site'))) {
		    $obj = app(Config::get('entrust.site'));
		    $site = $site->{$obj->primaryKey};
        }

		if ($this->auth->guest() || !$request->user()->ability($roles, $permissions, [ 'validate_all' => $validateAll ], $site)) {
			abort(403);
		}

		return $next($request);
	}
}
