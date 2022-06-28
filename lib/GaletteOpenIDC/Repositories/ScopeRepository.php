<?php

declare(strict_types=1);

/**
 *  OpenID Connect plugin for Galette
 *
 *  PHP version 7
 *
 *  This file is part of 'OpenID Connect plugin for Galette'.
 *
 *  OpenID Connect Plugin for Galette is free software: you can redistribute it
 *  and/or modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation, either version 3 of the License,
 *  or (at your option) any later version.
 *
 *  OpenID Connect Plugin for Galette is distributed in the hope that it will
 *  be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General
 *  Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License along
 *  with OpenID Connect Plugin for Galette. If not, see
 *  <http://www.gnu.org/licenses/>.
 *
 *  @category Plugins
 *  @package  OpenID Connect plugin for Galette
 *
 *  @author	Manuel Hervouet <manuelh78dev@ik.me>
 *  @author	Florian Hatat <github@hatat.me>
 *  @copyright Manuel Hervouet (c) 2021
 *  @copyright Florian Hatat (c) 2022
 *  @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0
 */

namespace GaletteOpenIDC\Repositories;

use GaletteOpenIDC\Entities\ScopeEntity;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

final class ScopeRepository implements ScopeRepositoryInterface
{
	public static $scopes = [
		'basic' => [
			'description' => 'Basic details about you',
		],
		'email' => [
			'description' => 'Your email address',
		],
		'openid' => [
			'description' => 'OpenID Connect user information',
		],
		'galette' => [
			'description' => 'Galette membership status and information',
		],
		'profile' => [
			'description' => 'Extended personnal user information',
		],
	];

	public static function getScopes()
	{
		return self::$scopes;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getScopeEntityByIdentifier($scopeIdentifier)
	{
		if (\array_key_exists($scopeIdentifier, self::$scopes) === false) {
			return;
		}

		$scope = new ScopeEntity();
		$scope->setIdentifier($scopeIdentifier);

		return $scope;
	}

	/**
	 * {@inheritDoc}
	 */
	public function finalizeScopes(
		array $scopes,
		$grantType,
		ClientEntityInterface $clientEntity,
		$userIdentifier = null
	) {
		/*TODO : ?
				// Example of programatically modifying the final scope of the access token
				if ((int) $userIdentifier === 1) {
					$scope = new ScopeEntity();
					$scope->setIdentifier('email');
					$scopes[] = $scope;
				}
		 */
		return $scopes;
	}
}
