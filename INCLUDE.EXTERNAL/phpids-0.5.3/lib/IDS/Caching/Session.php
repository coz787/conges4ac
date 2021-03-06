<?php

/**
 * PHPIDS
 * 
 * Requirements: PHP5, SimpleXML
 *
 * Copyright (c) 2007 PHPIDS group (http://php-ids.org)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; version 2 of the license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * PHP version 5.1.6+
 * 
 * @category Security
 * @package  PHPIDS
 * @author   Mario Heiderich <mario.heiderich@gmail.com>
 * @author   Christian Matthies <ch0012@gmail.com>
 * @author   Lars Strojny <lars@strojny.net>
 * @license  http://www.gnu.org/licenses/lgpl.html LGPL
 * @link     http://php-ids.org/
 */

require_once 'IDS/Caching/Interface.php';

/**
 * File caching wrapper
 *
 * This class inhabits functionality to get and set cache via session.
 *
 * @category  Security
 * @package   PHPIDS
 * @author    Christian Matthies <ch0012@gmail.com>
 * @author    Mario Heiderich <mario.heiderich@gmail.com>
 * @author    Lars Strojny <lars@strojny.net>
 * @copyright 2007 The PHPIDS Group
 * @license   http://www.gnu.org/licenses/lgpl.html LGPL
 * @version   Release: $Id: Session.php,v 1.1.1.1.2.1 2013/04/11 05:50:03 pavet Exp $
 * @link      http://php-ids.org/
 * @since     Version 0.4
 */
class IDS_Caching_Session implements IDS_Caching_Interface
{

    /**
     * Caching type
     *
     * @var string
     */
    private $type = null;

    /**
     * Cache configuration
     *
     * @var array
     */
    private $config = null;

    /**
     * Holds an instance of this class
     *
     * @var object
     */
    private static $cachingInstance = null;

    /**
     * Constructor
     *
     * @param string $type caching type
     * @param array  $init the IDS_Init object
     * 
     * @return void
     */
    public function __construct($type, $init) 
    {
        $this->type   = $type;
        $this->config = $init->config['Caching'];
    }

    /**
     * Returns an instance of this class
     *
     * @param string $type   caching type
     * @param array  $init the IDS_Init object
     * 
     * @return object $this
     */
    public static function getInstance($type, $init) 
    {

        if (!self::$cachingInstance) {
            self::$cachingInstance = new IDS_Caching_Session($type, $init);
        }

        return self::$cachingInstance;
    }

    /**
     * Writes cache data into the session
     *
     * @param array $data the caching data
     * 
     * @return object $this
     */
    public function setCache(array $data) 
    {

        $_SESSION['PHPIDS'][$this->type] = $data;
        return $this;
    }

    /**
     * Returns the cached data
     *
     * Note that this method returns false if either type or file cache is not set
     *
     * @return mixed cache data or false
     */
    public function getCache() 
    {

        if ($this->type && $_SESSION['PHPIDS'][$this->type]) {
            return $_SESSION['PHPIDS'][$this->type];
        }

        return false;
    }
}

/**
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 */
