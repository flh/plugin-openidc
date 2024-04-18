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
use GaletteOpenIDC\Authorization\UserAuthorizationException;
use GaletteOpenIDC\Authorization\UserHelper;
use GaletteOpenIDC\Tools\Config;
use GaletteOpenIDC\Tools\Debug as Debug;
use Psr\Container\ContainerInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

final class LoginController extends AbstractPluginController
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
		parent::__construct($container);
		$this->container = $container;
		$this->config = $container->get(Config::class);
	}

	public function login(Request $request, Response $response): Response
	{
		Debug::logRequest('login()', $request);

		if ($request->getMethod() === 'GET') {
			$query_params = $request->getQueryParams();
			$redirect_url = $query_params['redirect_url'] ?? null;

			if ($redirect_url) {
				$url = \urldecode($redirect_url);
				$url_query = \parse_url($url, \PHP_URL_QUERY);
				\parse_str($url_query, $url_args);
				$_SESSION['request_args'] = $url_args;
			}

			if (OPENIDC_DEBUGSESSION) {
				Debug::log('GET _SESSION = ' . Debug::printVar($_SESSION));
			}

			// display page
			return $this->view->render(
				$response,
				$this->getTemplate('openidc_login'),
				$this->prepareVarsForm(),
			);
		}

		if (OPENIDC_DEBUGSESSION) {
			Debug::log('POST _SESSION = ' . Debug::printVar($_SESSION));
		}

		// Get all POST parameters
		$params = (array) $request->getParsedBody();

		//Try login
		$_SESSION['isLoggedIn'] = 'no';
		$_SESSION['user_id'] = $uid = UserHelper::login($this->container, $params['username'], $params['password']);
		//if($params['username'] == 'manuel') $loginSuccessful = true;
		Debug::log("UserHelper::login({$params['username']}) return '{$uid}'");

		if (0 === $uid) {
			return $this->view->render(
				$response,
				$this->getTemplate('openidc_login'),
				\array_merge(
					$this->prepareVarsForm(),
					[
						'username' => $params['username'],
						'errorMessage' => _T('Check your login / email or password.', 'oauth2'),
					],
				),
			);
		}

		//check rights with scopes
		$options = UserHelper::mergeOptions($this->config, $_SESSION['request_args']['client_id'], \explode(' ', $_SESSION['request_args']['scope']));

		try {
			UserHelper::getUserData($this->container, $uid, $options);
		} catch (UserAuthorizationException $e) {
			UserHelper::logout($this->container);
			Debug::log('login() check rights error ' . $e->getMessage());

			return $this->view->render(
				$response,
				$this->getTemplate('openidc_login'),
				\array_merge(
					$this->prepareVarsForm(),
					[
						'username' => $params['username'],
						'errorMessage' => $e->getMessage(),
					],
				),
			);
		}

		$_SESSION['isLoggedIn'] = 'yes';

		// User is logged in, redirect them to authorize
		$url_params = [
			'response_type' => $_SESSION['request_args']['response_type'],
			'client_id' => $_SESSION['request_args']['client_id'],
			'scope' => $_SESSION['request_args']['scope'],
			'state' => $_SESSION['request_args']['state'],
			'redirect_uri' => $_SESSION['request_args']['redirect_uri'],
		];
		if(isset($_SESSION['request_args']['nonce'])) {
			$url_params['nonce'] = $_SESSION['request_args']['nonce'];
		}

		$url = $this->routeparser->urlFor(OPENIDC_PREFIX . '_authorize', [], $url_params);

		$response = new Response();

		return $response->withHeader('Location', $url)
			->withStatus(302);
	}

	public function logout(Request $request, Response $response): Response
	{
		Debug::logRequest('logout()', $request);
		UserHelper::logout($this->container);

		$_SESSION['user_id'] = null;
		$_SESSION['isLoggedIn'] = 'no';
		$client_id = $_SESSION['request_args']['client_id'];
		$_SESSION['request_args'] = [];

		//By default : client_id.redirect_logout else '/'
		$redirect_logout = '/';

		if ($client_id) {
			$redirect_logout = $this->config->get("{$client_id}.redirect_logout", $redirect_logout);
		}

		Debug::log("logout():url_logout for client:'{$client_id}' = '{$redirect_logout}'");

		//Add a url redirection in config.yml : $client_id:   redirect_logout:"https:\\xxx");
		return $response->withHeader('Location', $redirect_logout)->withStatus(302);
	}

	private function prepareVarsForm()
	{
		$client_id = $_SESSION['request_args']['client_id'];
		$application = $this->config->get("{$client_id}.title", 'noname');
		$page_title = _T('Please sign in for', OPENIDC_PREFIX) . " '{$application}'";

		return [
			'page_title' => $page_title,
			'application' => $application,
			'prefix' => OPENIDC_PREFIX,
			//TODO:
			//'path_css' => $this->router->pathFor('slash') . '../plugins/plugin-oauth2/webroot/',
		];
	}
}
