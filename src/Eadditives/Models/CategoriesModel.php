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
namespace Eadditives\Models;

use \Eadditives\MyRequest;

/**
 * CategoriesModel
 *
 * Fetch additives categories data from database.
 *
 * @package Eadditives
 * @author  p.petrov
 */
class CategoriesModel extends Model {

    const CACHE_KEY = 'cats_';
    const CACHE_TTL = 600;  // 10 minutes   

    /**
     * Get a list of categories.
     * @param  array $criteria Filtering criteria.
     * @throws ModelException On any SQL error.
     * @return array 
     */ 
    public function getAll($criteria = array()) {
        $criteria = $this->getDatabaseCriteria($criteria);

        // get cached result
        $cacheKey = $this->cache->genKey(self::CACHE_KEY, implode($criteria));
        if ($this->cache->exists($cacheKey)) {
            $data = unserialize($this->cache->get($cacheKey));

            // set HTTP entity tag (ETag) header
            $uniqueId = '';
            foreach ($data as $row) {
                $dt = new \DateTime($row['last_update']);
                $uniqueId .= $dt->getTimestamp();
            }
            $this->setCacheHeaders($uniqueId);

            return $data;
        }             

        $sql = "SELECT c.id, c.category, c.last_update, p.name,
            (SELECT COUNT(id) FROM __Additive as a WHERE a.category_id=c.id) as additives
            FROM __AdditiveCategory as c
            LEFT JOIN __AdditiveCategoryProps as p ON p.category_id = c.id
            WHERE p.locale_id = :locale_id";

        $sql = self::normalizeTables($sql);

        // apply sort criteria
        if (!is_null($criteria[MyRequest::PARAM_SORT])) {
            $this->validateCriteria($criteria, MyRequest::PARAM_SORT, array('id', 'name', 'last_update'));
            $sql .= sprintf(" ORDER BY %s %s", 
                $criteria[MyRequest::PARAM_SORT], 
                $criteria[MyRequest::PARAM_ORDER]);
        }

        try {

            $statement = $this->dbConnection->executeQuery($sql, array(
                'locale_id' => $criteria[MyRequest::PARAM_LOCALE]
            ));
            $result = $statement->fetchAll();

            // $this->validateResult($result);

            // format results
            $uniqueId = '';
            $items = array();
            foreach ($result as $row) {
                // ISO-8601 datetime format
                $dt = new \DateTime($row['last_update']);
                $row['last_update'] = $dt->format(\DateTime::ISO8601);
                // add resource url
                $row['url'] = BASE_URL . '/categories/' . $row['id'];
                // add updated row
                $items[] = $row;
                // result unique-id 
                // XXX: large string performance?!
                $uniqueId .= $dt->getTimestamp();                
            }

            // write to cache
            if ($this->cache->set($cacheKey, serialize($items), self::CACHE_TTL)) {
                // set HTTP entity tag (ETag) header
                $this->setCacheHeaders($uniqueId, self::CACHE_TTL);
            }

            return $items;

        } catch (\Slim\Exception\Stop $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ModelException('SQL Error!', $e->getCode(), $e);
        }
    }

    /**
     * Get information about single category.
     * @param  string $id category id
     * @param  array $criteria Filtering criteria.
     * @throws ModelException On any SQL error.
     * @return array 
     */ 
    public function getSingle($id, $criteria = array()) {
        $criteria = $this->getDatabaseCriteria($criteria);

        // get cached result
        $cacheKey = $this->cache->genKey(self::CACHE_KEY, $criteria[MyRequest::PARAM_LOCALE], $id);
        if ($this->cache->exists($cacheKey)) {
            $data = $this->cache->hget($cacheKey);
            
            // set HTTP entity tag (ETag) header
            $this->setCacheHeaders($data['last_update']);

            return $data;
        }

        $sql = "SELECT c.id, p.name, p.description, p.last_update,
            (SELECT COUNT(id) FROM __Additive as a WHERE a.category_id=c.id) as additives
            FROM __AdditiveCategory as c
            LEFT JOIN __AdditiveCategoryProps as p ON p.category_id = c.id
            WHERE c.id = :category_id AND p.locale_id = :locale_id LIMIT 1";

        $sql = self::normalizeTables($sql);

        try {

            $statement = $this->dbConnection->executeQuery($sql, array(
                'locale_id' => $criteria[MyRequest::PARAM_LOCALE],
                'category_id' => $id
            ));
            $result = $statement->fetch();

            $this->validateResult($result); 

            // ISO-8601 datetime format
            $dt = new \DateTime($result['last_update']);
            $result['last_update'] = $dt->format(\DateTime::ISO8601);
            // add resource url
            $result['url'] = BASE_URL . '/categories/' . $result['id'];

            // write to cache
            if ($this->cache->hset($cacheKey, $result, self::CACHE_TTL)) {
                // set HTTP entity tag (ETag) header
                $this->setCacheHeaders($result['last_update'], self::CACHE_TTL);
            }

            return $result;

        } catch (\Slim\Exception\Stop $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ModelException('SQL Error!', $e->getCode(), $e);
        }
    }   

}
?>