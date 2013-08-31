<?php
/*
 * E-additives REST API Server
 * Copyright (C) 2013 VEXELON.NET Services
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

use Doctrine\Common\ClassLoader;

// Establish database connection
$databaseSettings = unserialize(DB_SETTINGS);

$dbConfig = new \Doctrine\DBAL\Configuration();
if (SHOW_SQL)
    $dbConfig->setSQLLogger(new \Eadditives\Loggers\MySQLLogger());

$dbConnectionParams = array(
    'driver' => 'pdo_mysql',
    'host' => $databaseSettings['host'],
    'dbname' => $databaseSettings['database'],
    'user' => $databaseSettings['user'],
    'password' => $databaseSettings['password'],
    'charset ' => $databaseSettings['charset '],
);
$dbConnection = \Doctrine\DBAL\DriverManager::getConnection($dbConnectionParams, $dbConfig);

// Configure REST App
$app = new \Slim\Slim(array(
    'debug' => DEBUG,
    'log.level' => DEBUG ? \Slim\Log::DEBUG : \Slim\Log::WARN,
    'log.enabled' => true,
    'http.version' => '1.1'
    ));
$app->setName(APP_NAME);

// Register Logger
use Eadditives;
$logger = new \Slim\Log(new \Eadditives\Loggers\MyLogger());

// Initialize Response mediators
$app->view(new \Eadditives\Views\JsonView($app, $logger));
$app->add(new \Eadditives\Views\JsonMiddleware($app, $logger));
	
// Run API
require 'api.php';

$app->run();

?>
