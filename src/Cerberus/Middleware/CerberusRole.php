<?php namespace Michalisantoniou6\Cerberus\Middleware;

/**
 * This file is part of Cerberus,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Michalisantoniou6\Cerberus
 */

use Closure;
use Illuminate\Contracts\Auth\Guard;

class CerberusRole
{
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
     * @param  \Illuminate\Http\Request $request
     * @param  Closure $next
     * @param  $roles
     *
     * @return mixed
     */
	public function handle($request, Closure $next, ...$roles)
	{
	    if ($this->auth->guest() || !$request->user()->hasRole($roles, false)) {
			abort(403);
		}

		return $next($request);
	}
}
