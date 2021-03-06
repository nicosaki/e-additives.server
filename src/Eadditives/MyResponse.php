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

namespace Eadditives;

use \Eadditives\Views\JsonView;

/**
 * MyResponse
 *
 * Prepares, formats and then channels Model fetched data as HTTP JSON response.
 *
 * @package Eadditives
 * @author  p.petrov
 */
class MyResponse {
    
    /**
     * HTTP status codes
     */
    const HTTP_STATUS_OK = 200;
    const HTTP_STATUS_BAD_REQUEST = 400;
    const HTTP_STATUS_UNAUTHORIZED = 401;
    const HTTP_STATUS_FORBIDDEN = 403;
    const HTTP_STATUS_NOT_FOUND = 404;
    const HTTP_STATUS_PRECONDITION_FAILED = 412;
    const HTTP_STATUS_ERROR = 500;
    const HTTP_STATUS_ERROR_SERVICE_UNAVAILABLE = 503;

    protected $app;

    function __construct($app) {
        $this->app = $app;
    }

    public function etag($uniqueId) {
        if (HTTP_CACHE && isset($uniqueId)) {
            $this->app->etag($uniqueId);
        }
    }

    function render($status, $results) {
        //$this->app->response->headers->set('X-Alfa-Type', 'Broderbund');
        $this->app->render($status, $results);      
    }

    public function renderOK($results) {
        $this->render(self::HTTP_STATUS_OK, $results);
    }

    public function renderError($errorMessage, $errorCode = null) {
        $this->app->render(self::HTTP_STATUS_ERROR, 
            self::newErrorObject(self::HTTP_STATUS_ERROR, $errorMessage, $errorCode));
    }

    public static function newErrorObject($status, $errorMessage, $errorCode = null) {
        $dt = new \DateTime();
        $errorObj = array(
            'timestamp' => $dt->getTimestamp(),
            'status' => $status,
            'msg' => $errorMessage);

        if (!is_null($errorCode)) {
            $errorObj['code'] = $errorCode;
        }

        return $errorObj;
    }
}

?>