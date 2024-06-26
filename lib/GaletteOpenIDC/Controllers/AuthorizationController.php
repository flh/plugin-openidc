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

namespace GaletteOpenIDC\Controllers;

use DI\Attribute\Inject;
use Galette\Controllers\AbstractPluginController;
use GaletteOpenIDC\Entities\UserEntity;
use GaletteOpenIDC\Tools\Config as Config;
use GaletteOpenIDC\Tools\Debug as Debug;
use League\OAuth2\Server\AuthorizationServer;
use Psr\Container\ContainerInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

final class AuthorizationController extends AbstractPluginController
{
	/**
	 * @var array
	 */
	#[Inject("Plugin Galette OpenID Connect")]
	protected $module_info;
	protected $container;
	protected $config;

	// constructor receives container instance
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
		$this->config = $this->container->get(Config::class);
	}

	public function authorize(Request $request, Response $response): Response
	{
		Debug::logRequest('authorization/authorize()', $request);

		$server = $this->container->get(AuthorizationServer::class);

		try {
			$queryParams = $request->getQueryParams();

			//Save redirect_uri (it's not possible with Sessions)
			if (isset($queryParams['redirect_uri'])) {
				$key = $queryParams['client_id'] . '.redirect_uri';
				$v = $queryParams['redirect_uri'];

				if ($this->config->get($key, '') === '') {
					Debug::log("Auto add redirect_uri to config.yml : '{$key}' = '{$v}' ");
					$this->config->set($key, $v);
					$this->config->writeFile();
					Debug::log('Auto add redirect_uri ok.');
				}
			}

			// Validate the HTTP request and return an AuthorizationRequest object.
			// The auth request object can be serialized into a user's session
			$authRequest = $server->validateAuthorizationRequest($request);

		$user = new UserEntity();
		$user->setIdentifier($_SESSION['user_id']);
			$authRequest->setUser($user);

			//TODO : Scopes implementation
			if (0) {
				if ($request->getMethod() === 'GET') {
					//$queryParams = $request->getQueryParams();
					$scopes = isset($queryParams['scope']) ? \explode(' ', $queryParams['scope']) : ['default'];

					return $this->container->get(Twig::class)->render(
						$response,
						'authorize.twig',
						[
							'pageTitle' => 'Authorize',
							'clientName' => $authRequest->getClient()->getName(),
							'scopes' => $scopes,
						],
					);
				}

				$params = (array) $request->getParsedBody();
			} else {
				$params = [];
				$params['authorized'] = 'true';
			}

			// Once the user has approved or denied the client update the status
			// (true = approved, false = denied)
			$authorized = 'true' === $params['authorized'];
			$authRequest->setAuthorizationApproved($authorized);

			// Return the HTTP redirect response
			$r = $server->completeAuthorizationRequest($authRequest, $response);
			Debug::log('authorization/authorize() exit ok');

			return $r;
		} catch (OAuthServerException $exception) {
			return $exception->generateHttpResponse($response);
		} catch (\Exception $exception) {
			$body = $response->getBody();
			$body->write($exception->getMessage());

			return $response->withStatus(500)->withBody($body);
		}
	}

	public function token(Request $request, Response $response): Response
	{
		Debug::logRequest('authorization/token()', $request);
		$server = $this->container->get(AuthorizationServer::class);
		$params = (array) $request->getParsedBody(); //POST

		try {
			// Try to respond to the access token request
			$r = $server->respondToAccessTokenRequest($request, $response);
			Debug::log('authorization/token() exit ok');

			return $r;
		} catch (OAuthServerException $exception) {
			Debug::log('authorization/Exception 1: ' . $exception->getMessage());
			// All instances of OAuthServerException can be converted to a PSR-7 response
			return $exception->generateHttpResponse($response);
		} catch (\Exception $exception) {
			Debug::log('authorization/Exception 2: ' .
			$exception->getMessage() . '<br>' . $exception->getTraceAsString(), );
			// Catch unexpected exceptions
			$body = $response->getBody();
			$body->write($exception->getMessage());

			return $response->withStatus(500)->withBody($body);
		}
	}
}
