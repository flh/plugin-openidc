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

use GaletteOpenIDC\Entities\AuthCodeEntity;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

final class AuthCodeRepository implements AuthCodeRepositoryInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity): void
	{
		// Some logic to persist the auth code to a database
	}

	/**
	 * {@inheritDoc}
	 */
	public function revokeAuthCode($codeId): void
	{
		// Some logic to revoke the auth code in a database
	}

	/**
	 * {@inheritDoc}
	 */
	public function isAuthCodeRevoked($codeId)
	{
		return false; // The auth code has not been revoked
	}

	/**
	 * {@inheritDoc}
	 */
	public function getNewAuthCode()
	{
		return new AuthCodeEntity();
	}
}
