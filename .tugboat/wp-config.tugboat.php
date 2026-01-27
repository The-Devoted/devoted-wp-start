<?php
// Define Tugboat's database credentials.
define('DB_NAME', 'tugboat');
define('DB_USER', 'tugboat');
define('DB_PASSWORD', 'tugboat');
define('DB_HOST', 'mysql');

// Set our Tugboat hostname.
define( 'WP_HOME', 'https://' . getenv('TUGBOAT_SERVICE_URL_HOST') );

// Define the location where WordPress Core is installed.
define( 'WP_SITEURL', 'https://' . getenv('TUGBOAT_SERVICE_URL_HOST'));
