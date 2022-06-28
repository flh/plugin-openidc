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

use GaletteOpenIDC\Controllers\ApiController;
use GaletteOpenIDC\Controllers\AuthorizationController;
use GaletteOpenIDC\Controllers\LoginController;
use GaletteOpenIDC\Controllers\ConfigurationController;
use GaletteOpenIDC\Middleware\Authentication;

//Include specific classes (league/oauth2_server and tools)
require_once 'vendor/autoload.php';

//Constants and classes from plugin
require_once $module['root'] . '/_config.inc.php';

require_once '_dependencies.php';

//login is always called by a http_redirect
$this->map(['GET', 'POST'], '/login', [LoginController::class, 'login'])->setName(OPENIDC_PREFIX . '_login');
$this->map(['GET', 'POST'], '/logout', [LoginController::class, 'logout'])->setName(OPENIDC_PREFIX . '_logout');

$this->map(['GET', 'POST'], '/authorize', [AuthorizationController::class, 'authorize'])
    ->setName(OPENIDC_PREFIX . '_authorize')->add(Authentication::class);
$this->post('/access_token', [AuthorizationController::class, 'token'])->setName(OPENIDC_PREFIX . '_token');

$this->get('/user', [ApiController::class, 'user'])->setName(OPENIDC_PREFIX . '_user');

$this->get('/openid-configuration', [ConfigurationController::class, 'openid'])->setName(OPENIDC_PREFIX . '_openid_configuration');
$this->get('/jwk', [ConfigurationController::class, 'json_web_key'])->setName(OPENIDC_PREFIX . '_json_web_key');
