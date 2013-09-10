<?php
/*
 * e-additives.server RESTful API
 * Copyright (C) 2013 VEXELON.NET Services
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Eadditives\Cache;

/**
 * CacheInterface
 *
 * Cache server commands interface.
 *
 * @package Eadditives
 * @author  p.petrov
 */
interface CacheInterface {

	public function set($key, $value, $ttl = 0);

	public function hset($key, array $values, $ttl = 0);

	public function get($key);

	public function hget($key);

	public function exists($key);

	public function delete($key);
}

?>