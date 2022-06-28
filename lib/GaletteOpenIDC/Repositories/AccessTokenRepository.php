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

use GaletteOpenIDC\Entities\AccessTokenEntity;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use Idaas\OpenID\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface as LeagueAccessTokenEntityInterface;
use Idaas\OpenID\Repositories\AccessTokenRepositoryInterface;

final class AccessTokenRepository implements AccessTokenRepositoryInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function persistNewAccessToken(LeagueAccessTokenEntityInterface $accessTokenEntity) : void
	{
		// Some logic here to save the access token to a database
	}

	/**
	 * {@inheritDoc}
	 */
	public function revokeAccessToken($tokenId): void
	{
		// Some logic here to revoke the access token
	}

	/**
	 * {@inheritDoc}
	 */
	public function isAccessTokenRevoked($tokenId)
	{
		return false; // Access token hasn't been revoked
	}

	/**
	 * {@inheritDoc}
	 */
	public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
	{
		$accessToken = new AccessTokenEntity();
		$accessToken->setClient($clientEntity);

		foreach ($scopes as $scope) {
			$accessToken->addScope($scope);
		}
		$accessToken->setUserIdentifier($userIdentifier);

		return $accessToken;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getAccessToken($tokenId)
	{
		// TODO
	}

	/**
	 * {@inheritDoc}
	 */
	public function storeClaims(LeagueAccessTokenEntityInterface $token, array $claims)
	{
		foreach($claims as $claim)
		{
			$token->addClaim($claim);
		}
	}
}
