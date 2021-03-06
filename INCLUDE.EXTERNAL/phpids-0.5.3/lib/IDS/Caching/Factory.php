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

/**
 * Caching factory
 *
 * This class is used as a factory to load the correct concrete caching
 * implementation.
 *
 * @category  Security
 * @package   PHPIDS
 * @author    Christian Matthies <ch0012@gmail.com>
 * @author    Mario Heiderich <mario.heiderich@gmail.com>
 * @author    Lars Strojny <lars@strojny.net>
 * @copyright 2007 The PHPIDS Group
 * @license   http://www.gnu.org/licenses/lgpl.html LGPL
 * @version   Release: $Id: Factory.php,v 1.1.1.1.2.1 2013/04/11 05:50:03 pavet Exp $
 * @link      http://php-ids.org/
 * @since     Version 0.4
 */
class IDS_Caching
{

    /**
     * Factory method
     *
     * @param array  $init the IDS_Init object
     * @param string $type the caching type
     * 
     * @return object the caching facility
     */
    public static function factory($init, $type) 
    {
        
    	$object  = false;
        $wrapper = preg_replace(
			'/\W+/m', 
			null, 
			ucfirst($init->config['Caching']['caching'])
		);
        $class   = 'IDS_Caching_' . $wrapper;
        $path    = dirname(__FILE__) . DIRECTORY_SEPARATOR . 
            $wrapper . '.php';

        if (file_exists($path)) {
            include_once $path;

            if (class_exists($class)) {
                $object = call_user_func(array($class, 'getInstance'), 
                    $type, $init);
            }
        }

        return $object;
    }
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 */