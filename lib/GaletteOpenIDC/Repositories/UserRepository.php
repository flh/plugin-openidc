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


namespace GaletteOpenIDC\Repositories;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use Idaas\OpenID\Repositories\UserRepositoryInterface;
use Idaas\OpenID\Repositories\UserRepositoryTrait;
use League\OAuth2\Server\Entities\UserEntityInterface;
use Psr\Container\ContainerInterface as ContainerInterface;
use Galette\Entity\Adherent;
use GaletteOpenIDC\Entities\UserEntity;
use Galette\Entity\Status as GaletteStatus;

final class UserRepository implements UserRepositoryInterface
{
	use UserRepositoryTrait;

	private $container;

	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	public function getAttributes(UserEntityInterface $userEntity, $claims, $scopes)
	{
		$attributes = [];
		$zdb = $this->container->get('zdb');
		$adherent = new Adherent($zdb);
		$adherent->load(intval($userEntity->getIdentifier()));

		if(in_array('profile', $scopes)) {
			$attributes['family_name'] = \ucwords(\mb_strtolower($adherent->name));
			$attributes['given_name'] = \ucwords(\mb_strtolower($adherent->surname));
			$attributes['name'] = $adherent->sfullname;
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
			$updated_at = \DateTime::createFromFormat('!' . __("Y-m-d"), $adherent->modification_date);
			$attributes['updated_at'] = $updated_at->getTimestamp();
		}

		if(in_array('email', $scopes)) {
			$attributes['email'] = $adherent->email;
		}

		if(in_array('galette', $scopes)) {
			$attributes['galette_uptodate'] = ($adherent->isActive() && $adherent->isUp2Date()) || $adherent->isAdmin();
			$attributes['galette_status'] = $adherent->status;
			$attributes['galette_status_priority'] = (new GaletteStatus($zdb, $adherent->status))->third;
			$attributes['galette_staff'] = $adherent->isStaff();

			$attributes['galette_groups'] = [];
			foreach($adherent->getGroups() as $galette_group) {
				$attributes['galette_groups'][] = self::normalizeGaletteGroup($galette_group);
			}

			$attributes['galette_managed_groups'] = [];
			foreach($adherent->getManagedGroups() as $galette_group) {
				$attributes['galette_managed_groups'][] = self::normalizeGaletteGroup($galette_group);
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

	private static function normalizeGaletteGroup($group)
	{
		$path = [];
		$current_group = $group;
		while($current_group)
		{
			array_unshift($path, $current_group->getName());
			$current_group = $current_group->getParentGroup();
		}
		return $path;
	}

	public function getUserInfoAttributes(UserEntityInterface $userEntity, $claims, $scopes)
	{
		return $this->getAttributes($userEntity, $claims, $scopes);
	}

	public function getUserByIdentifier($identifier): ?UserEntityInterface
	{
		$user = new UserEntity();
		$user->setIdentifier($identifier);
		return $user;
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
