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

use GaletteOpenIDC\Entities\ClientEntity;
use GaletteOpenIDC\Tools\Config as Config;
use GaletteOpenIDC\Tools\Debug as Debug;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use Psr\Container\ContainerInterface as ContainerInterface;

final class ClientRepository implements ClientRepositoryInterface
{
	private $container;
	private $config;

	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
		$this->config = $this->container->get(Config::class);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getClientEntity($clientIdentifier)
	{
		$client = new ClientEntity();
		$client->setIdentifier($this->config->get("{$clientIdentifier}.id", $clientIdentifier));
		$client->setName($clientIdentifier);
		$client->setRedirectUri($this->config->get("{$clientIdentifier}.redirect_uri"));
		$client->setConfidential();

		Debug::log('getClientEntity() ' . Debug::printVar($client));

		return $client;
	}

	/**
	 * {@inheritDoc}
	 */
	public function validateClient($clientIdentifier, $clientSecret, $grantType)
	{
		if (!\preg_match('/galette_/', $clientIdentifier)) {
			Debug::log("validateClient({$clientIdentifier}) denied");

			return false;
		}

		$pwd = \password_hash($this->config->get('global.password'), \PASSWORD_BCRYPT);

		if (\password_verify($clientSecret, $pwd) === false) {
			return false;
		}

		return true;
	}
}
