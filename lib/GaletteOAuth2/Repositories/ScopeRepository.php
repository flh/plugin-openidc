<?php

declare(strict_types=1);

/**
 * Plugin OAuth2 for Galette Project
 *
 *  PHP version 7
 *
 *  This file is part of 'Plugin OAuth2 for Galette Project'.
 *
 *  Plugin OAuth2 for Galette Project is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  Plugin OAuth2 for Galette Project is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with Plugin OAuth2 for Galette Project. If not, see <http://www.gnu.org/licenses/>.
 *
 *  @category Plugins
 *  @package  Plugin OAuth2 for Galette Project
 *
 *  @author	Manuel Hervouet <manuelh78dev@ik.me>
 *  @copyright Manuel Hervouet (c) 2021
 *  @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0
 */

namespace GaletteOAuth2\Repositories;

use GaletteOAuth2\Entities\ScopeEntity;
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
