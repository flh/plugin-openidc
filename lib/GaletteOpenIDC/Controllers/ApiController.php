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
use GaletteOpenIDC\Tools\Debug;
use League\OAuth2\Server\ResourceServer;
use Idaas\OpenID\UserInfo;
use Psr\Container\ContainerInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

final class ApiController extends AbstractPluginController
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
		$this->config = $container->get(Config::class);
	}

	public function user(Request $request, Response $response): Response
	{
		Debug::logRequest('api/user()', $request);
		$userinfo = $this->container->get(UserInfo::class);
		return $userinfo->respondToUserInfoRequest($request, $response);
	}
}
