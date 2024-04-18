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
 *  @author    Manuel Hervouet <manuelh78dev@ik.me>
 *  @author    Florian Hatat <github@hatat.me>
 *  @copyright Manuel Hervouet (c) 2021
 *  @copyright Florian Hatat (c) 2022
 *  @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0
 */

namespace GaletteOpenIDC\Middleware;

use GaletteOpenIDC\Tools\Debug as Debug;
use DI\Container;
use Slim\Routing\RouteParser;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

final class Authentication
{
    private $container;

    public function __construct(Container $container)
    {
	    $this->container = $container;
	    $this->routeparser = $container->get(RouteParser::class);
    }

    public function __invoke(Request $request, RequestHandler $handler) //slim v4
    {
        $loggedIn = $_SESSION['isLoggedIn'] ?? '';

        if ('yes' !== $loggedIn) {
            $url = $this->routeparser->urlFor(
                OPENIDC_PREFIX . '_login',
                [],
                ['redirect_url' => $_SERVER['REQUEST_URI']],
            );
            Debug::log("Redirect to {$url}");

            $response = new \Slim\Psr7\Response();
            // If the user is not logged in, redirect them to login
            return $response->withHeader('Location', $url)
                ->withStatus(302);
        }

        // The user must be logged in, so pass this request
        // down the middleware chain
        $response = $handler->handle($request);

        // And pass the request back up the middleware chain.
        return $response;
    }
}
