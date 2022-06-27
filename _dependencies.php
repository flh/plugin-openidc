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
 *  @author    Manuel Hervouet <manuelh78dev@ik.me>
 *  @copyright Manuel Hervouet (c) 2021
 *  @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0
 */

use Defuse\Crypto\Key;
use GaletteOAuth2\Repositories\AccessTokenRepository;
use GaletteOAuth2\Repositories\AuthCodeRepository;
use GaletteOAuth2\Repositories\ClientRepository;
use GaletteOAuth2\Repositories\RefreshTokenRepository;
use GaletteOAuth2\Repositories\ScopeRepository;
use GaletteOAuth2\Repositories\UserRepository;
use GaletteOAuth2\Repositories\ClaimRepository;
use GaletteOAuth2\Tools\Config;
use GaletteOAuth2\Tools\Debug as Debug;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\ResourceServer;
use Psr\Container\ContainerInterface;
use Idaas\OpenID\Grant\AuthCodeGrant;
use Idaas\OpenID\RequestTypes\AuthenticationRequest;
use Idaas\OpenID\Session;

if (OAUTH2_LOG) {
    Debug::init();
}

$container = $this->getContainer();

$container->set(
    Config::class,
    static function (ContainerInterface $container) {
        return new GaletteOAuth2\Tools\Config(OAUTH2_CONFIGPATH . '/config.yml');
    },
);

$container->set(
    AuthorizationServer::class,
    function (ContainerInterface $container) {
        include OAUTH2_CONFIGPATH . '/encryption-key.php';

        // Setup the authorization server
        $server = new AuthorizationServer(
        // instance of ClientRepositoryInterface
            new ClientRepository($container),
            // instance of AccessTokenRepositoryInterface
            new AccessTokenRepository(),
            // instance of ScopeRepositoryInterface
            new ScopeRepository(),
            // path to private key
            'file://' . OAUTH2_CONFIGPATH . '/private.key',
            // encryption key
            Key::loadFromAsciiSafeString($encryptionKey),
        );

	$refreshTokenRepository = new RefreshTokenRepository();
	$claimRepository = new ClaimRepository();

        $grant = new AuthCodeGrant(
            new AuthCodeRepository(),
            // instance of RefreshTokenRepositoryInterface
	    $refreshTokenRepository,
	    $claimRepository,
	    new Session,
	    new DateInterval('PT10M'),
            new DateInterval('PT10M'),
        );

        // Enable the password grant on the server
        // with a token TTL of 1 hour
        $server->enableGrantType(
            $grant,
            // access tokens will expire after 1 hour
            new DateInterval('PT1H'),
        );

        $rt_grant = new RefreshTokenGrant($refreshTokenRepository);
        // new refresh tokens will expire after 1 month
        $rt_grant->setRefreshTokenTTL(new DateInterval('P1M'));

        // Enable the refresh token grant on the server
        $server->enableGrantType(
            $rt_grant,
            // new access tokens will expire after an hour
            new DateInterval('PT1H'),
        );

        //--
        $userRepository = new UserRepository($container); // instance of UserRepositoryInterface
        $grant = new \League\OAuth2\Server\Grant\PasswordGrant(
            $userRepository,
            $refreshTokenRepository,
        );

        $grant->setRefreshTokenTTL(new \DateInterval('P1M')); // refresh tokens will expire after 1 month

        // Enable the password grant on the server
        $server->enableGrantType(
            $grant,
            new \DateInterval('PT1H'), // access tokens will expire after 1 hour
        );

        // Enable the client credentials grant on the server
        $server->enableGrantType(
            new \League\OAuth2\Server\Grant\ClientCredentialsGrant(),
            new \DateInterval('PT1H'), // access tokens will expire after 1 hour
        );

        return $server;
    },
);

$container->set(
    ResourceServer::class,
    static function (ContainerInterface $container) {
        $publicKeyPath = 'file://' . OAUTH2_CONFIGPATH . '/public.key';

        return new ResourceServer(
            new AccessTokenRepository(),
            $publicKeyPath,
        );
    },
);
