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
 *  @author    Florian Hatat
 *  @copyright Florian Hatat (c) 2022
 *  @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0
 */

namespace GaletteOAuth2\Controllers;

use Galette\Controllers\AbstractPluginController;
use GaletteOAuth2\Repositories\ScopeRepository;
use GaletteOAuth2\Repositories\ClaimRepository;
use GaletteOAuth2\Tools\Config;
use GaletteOAuth2\Tools\Debug as Debug;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use League\OAuth2\Server\CryptKey;

final class ConfigurationController extends AbstractPluginController
{
    /**
     * @Inject("Plugin Galette OAuth2")
     */
    protected $module_info;
    protected $container;
    protected $config;

    // constructor receives container instance
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $this->container->get(Config::class);
    }

    public function openid(Request $request, Response $response): Response
    {
	Debug::logRequest('openid_configuration()', $request);
	$issuer = 'https://' . $_SERVER['HTTP_HOST'];
	$data = [
	    'issuer' => $issuer,
	    'authorization_endpoint' => $issuer . $this->router->pathFor(OAUTH2_PREFIX . '_authorize', [], []),
	    'jwks_uri' => $issuer . $this->router->pathFor(OAUTH2_PREFIX . '_json_web_key', [], []),
	    'token_endpoint' => $issuer . $this->router->pathFor(OAUTH2_PREFIX . '_token', [], []),
	    'response_types_supported' => ['code', 'id_token', 'token id_token'],
	    'subject_types_supported' => ['public'],
	    'id_token_signing_alg_values_supported' => ['RSA256'],
	    'scopes_supported' => array_keys(ScopeRepository::getScopes()),
	    'claims_supported' => ClaimRepository::getAllClaims(),
	];
	$response->getBody()->write(\json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
	return $response->withStatus(200)->withHeader('Content-type', 'application/json');
    }

    public function json_web_key(Request $request, Response $response): Response
    {
	$key = new CryptKey('file://' . OAUTH2_CONFIGPATH . '/public.key');
	$openssl_key = \openssl_pkey_get_public($key->getKeyPath());
	$key_details = \openssl_pkey_get_details($openssl_key);
	$key_data = ['use' => 'sig', 'kid' => 'signing key'];
	if($key_details['type'] == OPENSSL_KEYTYPE_RSA)
	{
	    $key_data['kty'] = 'RSA';
	    $key_data['n'] = rtrim(strtr(base64_encode($key_details['rsa']['n']), '+/', '-_'), '=');
	    $key_data['e'] = rtrim(strtr(base64_encode($key_details['rsa']['e']), '+/', '-_'), '=');
	}
	elseif($key_details['type'] == OPENSSL_KEYTYPE_EC)
	{
	    $key_data['kty'] = 'EC';
	    $key_data['crv'] = $key_details['ec']['curve_name'];
	    $key_data['x'] = rtrim(strtr(base64_encode($key_details['ec']['x']), '+/', '-_'), '=');
	    $key_data['y'] = rtrim(strtr(base64_encode($key_details['ec']['y']), '+/', '-_'), '=');
	}
	$data = ['keys' => [$key_data]];
	$response->getBody()->write(\json_encode($data));
	return $response->withStatus(200)->withHeader('Content-type', 'application/jwk-set+json');
    }
}
