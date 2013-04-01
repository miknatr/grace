<?php

// TODO config from .my.cnf

/* CONFIGURATION FOR MySQL tests */
define('TEST_MYSQLI_HOST', '127.0.0.1');
define('TEST_MYSQLI_PORT', 3306);
define('TEST_MYSQLI_NAME', 'root');
define('TEST_MYSQLI_PASSWORD', '1');
define('TEST_MYSQLI_DATABASE', 'tests');

/* CONFIGURATION FOR PostgreSQL tests */
define('TEST_PGSQL_HOST', 'localhost');
define('TEST_PGSQL_PORT', 5432);
define('TEST_PGSQL_NAME', 'postgres');
define('TEST_PGSQL_PASSWORD', '1');
define('TEST_PGSQL_DATABASE', 'tests');
