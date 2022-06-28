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

namespace GaletteOAuth2\Repositories;

use Idaas\OpenID\Repositories\ClaimRepositoryInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use GaletteOAuth2\Entities\ClaimEntity;

class ClaimRepository implements ClaimRepositoryInterface
{
	public static $scopeClaims = [
		'profile' => [
			['name', ClaimEntity::TYPE_USERINFO, false],
			['family_name', ClaimEntity::TYPE_USERINFO, false],
			['given_name', ClaimEntity::TYPE_USERINFO, false],
			['middle_name', ClaimEntity::TYPE_USERINFO, false],
			['nickname', ClaimEntity::TYPE_USERINFO, false],
			['preferred_username', ClaimEntity::TYPE_USERINFO, false],
			['profile', ClaimEntity::TYPE_USERINFO, false],
			['picture', ClaimEntity::TYPE_USERINFO, false],
			['website', ClaimEntity::TYPE_USERINFO, false],
			['gender', ClaimEntity::TYPE_USERINFO, false],
			['birthdate', ClaimEntity::TYPE_USERINFO, false],
			['zoneinfo', ClaimEntity::TYPE_USERINFO, false],
			['locale', ClaimEntity::TYPE_USERINFO, false],
			['updated_at', ClaimEntity::TYPE_USERINFO, false],
		],
		'email' => [
			['email', ClaimEntity::TYPE_USERINFO, false],
			['email_verified', ClaimEntity::TYPE_USERINFO, false],
		],
		'galette' => [
			['galette_uptodate', ClaimEntity::TYPE_USERINFO, false],
			['galette_status', ClaimEntity::TYPE_USERINFO, false],
			['galette_status_priority', ClaimEntity::TYPE_USERINFO, false],
			['galette_staff', ClaimEntity::TYPE_USERINFO, false],
			['galette_groups', ClaimEntity::TYPE_USERINFO, false],
			['galette_managed_groups', ClaimEntity::TYPE_USERINFO, false],
		],
	];

	public static function getScopeClaims()
	{
		return self::$scopeClaims;
	}

	public static function getAllClaims()
	{
		$res = [];
		foreach(self::getScopeClaims() as $claims) {
			foreach($claims as $claim) {
				$res[] = $claim[0];
			}
		}
		return $res;
	}

    /**
     * Return information about a claim.
     *
     * @param string $identifier The claim identifier
     *
     * @return ClaimEntityInterface|null
     */
	public function getClaimEntityByIdentifier($identifier, $type, $essential)
	{
		return new ClaimEntity($identifier, $type, $essential);
	}

    /**
     * @return ClaimEntityInterface[]
     */
	public function getClaimsByScope(ScopeEntityInterface $scope) : iterable
	{
		$res = [];
		foreach($this->getScopeClaims()[$scope->getIdentifier()] ?? [] as $claim)
		{
			$res[] = $this->getClaimEntityByIdentifier($claim[0], $claim[1], $claim[2]);
		}
		return $res;
	}

	public function claimsRequestToEntities(array $json = null)
	{
		$res = [];
		foreach ([ClaimEntity::TYPE_ID_TOKEN, ClaimEntity::TYPE_USERINFO] as $type) {
			if ($json != null && isset($json[$type])) {
				foreach ($json[$type] as $claim => $properties) {
					$res[] = $this->getClaimEntityByIdentifier($claim, $type, isset($properties) && isset($properties['essential']) ? $properties['essential'] : false);
				}
			}
		}
		return $res;
	}
}
