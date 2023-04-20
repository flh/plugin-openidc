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

use GaletteOpenIDC\Entities\RefreshTokenEntity;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

final class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity): void
	{
		// Some logic to persist the refresh token in a database
	}

	/**
	 * {@inheritDoc}
	 */
	public function revokeRefreshToken($tokenId): void
	{
		// Some logic to revoke the refresh token in a database
	}

	/**
	 * {@inheritDoc}
	 */
	public function isRefreshTokenRevoked($tokenId)
	{
		return false; // The refresh token has not been revoked
	}

	/**
	 * {@inheritDoc}
	 */
	public function getNewRefreshToken()
	{
		return null;
		return new RefreshTokenEntity();
	}
}
