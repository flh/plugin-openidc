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

namespace GaletteOAuth2\Entities;

use Idaas\OpenID\Entities\ClaimEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

class ClaimEntity implements ClaimEntityInterface
{
    const TYPE_ID_TOKEN = 'id_token';
	const TYPE_USERINFO = 'userinfo';

	use EntityTrait;

	private $type;
	private $essential;

	public function __construct($identifier, $type, $essential)
	{
		$this->setIdentifier($identifier);
		$this->type = $type;
		$this->essential = $essential;
	}

    /**
     * Get type of the claim
     *
     * @return string userinfo|id_token
     */
	public function getType()
	{
		return $this->type;
	}

    /**
     * Whether this is an essential claim
     *
     * @return boolean
     */
	public function getEssential()
	{
		return $this->essential;
	}
}
