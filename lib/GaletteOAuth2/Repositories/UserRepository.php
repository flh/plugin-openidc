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

namespace GaletteOAuth2\Repositories;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use IDaas\OpenID\Repositories\UserRepositoryInterface;
use IDaas\OpenID\Repositories\UserRepositoryTrait;
use Psr\Container\ContainerInterface as ContainerInterface;
use Galette\Entity\Adherent;
use GaletteOAuth2\Entities\UserEntity;
use Galette\Entity\Status as GaletteStatus;

final class UserRepository implements UserRepositoryInterface
{
	use UserRepositoryTrait;

	private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getAttributes(UserEntity $userEntity, $claims, $scopes)
	{
		$attributes = [
			'sub' => $userEntity->getIdentifier(),
		];

		$adherent = $userEntity->getAdherent();

		if(in_array('profile', $scopes)) {
			$attributes['family_name'] = \ucwords(\mb_strtolower($adherent->name));
			$attributes['given_name'] = \ucwords(\mb_strtolower($adherent->surname));
			$attributes['name'] = $norm_name = $adherent->surname . ' ' . \mb_strtoupper($adherent->name);
			$attributes['nickname'] = \mb_strtolower($adherent->nickname);
			$attributes['locale'] = $adherent->language;
			$attributes['preferred_username'] = $adherent->login;
			if($adherent->isMan())
			{
				$attributes['gender'] = 'male';
			}
			if($adherent->isWoman())
			{
				$attributes['gender'] = 'female';
			}
			$attributes['updated_at'] = $adherent->modification_date->getTimestamp();
		}

		if(in_array('email', $scopes)) {
			$attributes['email'] = $adherent->email;
		}

		if(in_array('galette', $scopes)) {
			$attributes['galette_uptodate'] = ($adherent->isActive() && $adherent->isUp2Date()) || $adherent->isAdmin();
			$attributes['galette_status'] = $adherent->id_statut;
			$attributes['galette_status_priority'] = (new GaletteStatus($adherent->status))->third;
			$attributes['galette_staff'] = $adherent->isStaff();

			$attributes['galette_groups'] = [];
			foreach($adherent->getGroups() as $galette_group) {
				$attributes['galette_groups'][] = normalizeGaletteGroup($galette_group);
			}

			$attributes['galette_managed_groups'] = [];
			foreach($adherent->getManagedGroups() as $galette_group) {
				$attributes['galette_managed_groups'][] = normalizeGaletteGroup($galette_group);
			}
		}

		if(in_array('phone', $scopes)) {
			$attributes['phone'] = $adherent->phone;
		}

		return $attributes;
	}

    private static function stripAccents($str)
	{
		//TODO seems shifted, "é" incorrectly replaced par "c"
        return \strtr(\utf8_decode($str), \utf8_decode('ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïñòóôõöøùúûüýÿĀāĂăĄąĆćĈĉĊċČčĎďĐđĒēĔĕĖėĘęĚěĜĝĞğĠġĢģĤĥĦħĨĩĪīĬĭĮįİıĲĳĴĵĶķĹĺĻļĽľĿŀŁłŃńŅņŇňŉŌōŎŏŐőŒœŔŕŖŗŘřŚśŜŝŞşŠšŢţŤťŦŧŨũŪūŬŭŮůŰűŲųŴŵŶŷŸŹźŻżŽžſƒƠơƯưǍǎǏǐǑǒǓǔǕǖǗǘǙǚǛǜǺǻǼǽǾǿ'), 'AAAAAAAECEEEEIIIIDNOOOOOOUUUUYsaaaaaaaeceeeeiiiinoooooouuuuyyAaAaAaCcCcCcCcDdDdEeEeEeEeEeGgGgGgGgHhHhIiIiIiIiIiIJijJjKkLlLlLlLlllNnNnNnnOoOoOoOEoeRrRrRrSsSsSsSsTtTtTtUuUuUuUuUuUuWwYyYZzZzZzsfOoUuAaIiOoUuUuUuUuUuAaAEaeOo');
	}

	private static function normalizeGaletteGroup($group_name)
	{
	}

    public function getUserInfoAttributes(UserEntity $userEntity, $claims, $scopes)
	{
		return $this->getAttributes($userEntity, $claims, $scope);
    }

    public function getUserByIdentifier($identifier): ?UserEntityInterface
	{
		$zdb = $this->container->get('zdb');
		$galette_user = new Adherent($zdb);
		$galette_user->load($identifier);
		return new UserEntity($galette_user);
    }

    /**
     * {@inheritDoc}
     */
    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    ): void {
        Debug::log("getUserEntityByUserCredentials({$username}, '***', {$grantType}) ");

		$uid = UserHelper::login($this->container, $username, $password);
		//TODO
    }
}
