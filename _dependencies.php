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

use Defuse\Crypto\Key;
use GaletteOpenIDC\Repositories\AccessTokenRepository;
use GaletteOpenIDC\Repositories\AuthCodeRepository;
use GaletteOpenIDC\Repositories\ClientRepository;
use GaletteOpenIDC\Repositories\RefreshTokenRepository;
use GaletteOpenIDC\Repositories\ScopeRepository;
use GaletteOpenIDC\Repositories\UserRepository;
use GaletteOpenIDC\Repositories\ClaimRepository;
use GaletteOpenIDC\Tools\Config;
use GaletteOpenIDC\Tools\Debug as Debug;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\ResourceServer;
use Psr\Container\ContainerInterface;
use Idaas\OpenID\Grant\AuthCodeGrant;
use Idaas\OpenID\Grant\ImplicitGrant;
use Idaas\OpenID\RequestTypes\AuthenticationRequest;
use Idaas\OpenID\ResponseTypes\BearerTokenResponse;
use Idaas\OpenID\Session;
use Idaas\OpenID\UserInfo;

if (OPENIDC_LOG) {
	Debug::init();
}

$container = $this->getContainer();

$container->set(
	Config::class,
	static function (ContainerInterface $container) {
		return new GaletteOpenIDC\Tools\Config(OPENIDC_CONFIGPATH . '/config.yml');
	},
);

$container->set(
	ClaimRepository::class,
	static function(ContainerInterface $container) {
		return new ClaimRepository();
	},
);

$container->set(
	AccessTokenRepository::class,
	static function(ContainerInterface $container) {
		return new AccessTokenRepository($container);
	},
);

$container->get(
	ClientRepository::class,
	static function(ContainerInterface $container) {
		return new ClientRepository($container);
	},
);

$container->get(
	ScopeRepository::class,
	static function(ContainerInterface $container) {
		return new ScopeRepository();
	},
);

$container->set(
	AuthorizationServer::class,
	function (ContainerInterface $container) {
		$conf = $container->get(Config::class);
		$encryptionKey = $conf->get('global.encryption_key');

		// Setup the authorization server
		$server = new AuthorizationServer(
		// instance of ClientRepositoryInterface
			$container->get(ClientRepository::class),
			// instance of AccessTokenRepositoryInterface
			$container->get(AccessTokenRepository::class),
			// instance of ScopeRepositoryInterface
			$container->get(ScopeRepository::class),
			// path to private key
			'file://' . OPENIDC_CONFIGPATH . '/private.key',
			// encryption key
		Key::loadFromAsciiSafeString($encryptionKey),
		// Custom BearerTokenResponse for OpenID Connect
		new BearerTokenResponse,
		);

		$refreshTokenRepository = new RefreshTokenRepository();
		$claimRepository = $container->get(ClaimRepository::class);

		$authCodeGrant = new AuthCodeGrant(
			new AuthCodeRepository(),
			// instance of RefreshTokenRepositoryInterface
			$refreshTokenRepository,
			$claimRepository,
			new Session,
			new DateInterval('PT10M'),
			new DateInterval('PT10M'),
		);

		$authCodeGrant->setIssuer('https://' . $_SERVER['HTTP_HOST']);
	
		// Enable the password grant on the server
		// with a token TTL of 1 hour
		$server->enableGrantType(
			$authCodeGrant,
			// access tokens will expire after 1 hour
			new DateInterval('PT1H'),
		);

		$userRepository = new UserRepository($container); // instance of UserRepositoryInterface
		$implicitGrant = new ImplicitGrant(
			$userRepository,
			$claimRepository,
			new DateInterval('PT10M'),
			new DateInterval('PT10M')
		);
		$implicitGrant->setIssuer('https://' . $_SERVER['HTTP_HOST']);

		$server->enableGrantType($implicitGrant, new DateInterval('PT1H'));

		$rt_grant = new RefreshTokenGrant($refreshTokenRepository);
		// new refresh tokens will expire after 1 month
		$rt_grant->setRefreshTokenTTL(new DateInterval('P1M'));

		// Enable the refresh token grant on the server
		$server->enableGrantType(
			$rt_grant,
			// new access tokens will expire after an hour
			new DateInterval('PT1H'),
		);

		return $server;
	},
);

$container->set(
	ResourceServer::class,
	static function (ContainerInterface $container) {
		$publicKeyPath = 'file://' . OPENIDC_CONFIGPATH . '/public.key';

		return new ResourceServer(
			$container->get(AccessTokenRepository::class),
			$publicKeyPath,
		);
	},
);

$container->set(
	UserInfo::class,
	static function (ContainerInterface $container) {
		return new UserInfo(
			new UserRepository($container),
			$container->get(AccessTokenRepository::class),
			$container->get(ResourceServer::class),
			$container->get(ClaimRepository::class),
		);
	},
);
