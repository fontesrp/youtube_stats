<?php

/**
 * /config/session.php
 *
 * This files sets up as global constants all the parameters necessary to
 * inicialize a session. This parameters are read from /config/config.ini. As
 * this project assumes the application will be used internally, only the
 * configuration necessary for accessing the database is loaded.
 */

declare(strict_types=1);

$config = parse_ini_file(__DIR__ . "/config.ini", true, INI_SCANNER_TYPED);

foreach ($config as $section => $params) {
    foreach ($params as $key => $value) {
        define(strtoupper($section . "_" . $key), $value, false);
    }
}
