<?php
/**
 * setup logging to syslog and raise verbosity if not default deployment
 * read credentials from cloudControl run-time environment
 * setup database connection using the credentials
 */

	
function get_credentials() {
	// read the credentials file
	$string = file_get_contents($_ENV['CRED_FILE'], false);
	if ($string == false) {
		throw new Exception('Could not read credentials file');
	}
	// the file contains a JSON string, decode it and return an associative array
	$creds = json_decode($string, true);

	if (!array_key_exists('MYSQLS', $creds)){
		throw new Exception('No MySQL credentials found. Please make sure you have added the mysqls addon.');
	}

	$database_host = $creds["MYSQLS"]["MYSQLS_HOSTNAME"];
	$database_name = $creds["MYSQLS"]["MYSQLS_DATABASE"];
	$database_user = $creds["MYSQLS"]["MYSQLS_USERNAME"];
	$database_password = $creds["MYSQLS"]["MYSQLS_PASSWORD"];

	return array(
		'driver'         => 'Pdo',
		'dsn'            => sprintf('mysql:dbname=%s;host=%s', $database_name, $database_host),
		'driver_options' => array(
			PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
		),
		'username' => $database_user,
		'password' => $database_password,
	);
}

$config = array();

// If the app is running on the cloudControl PaaS read the credentials
// from the environment. Local db credentials should be put in local.php
if (isset($_ENV['CRED_FILE'])) {
	$config['db'] = get_credentials();
}

$config['service_manager'] = array(
	'factories' => array(
		'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
		'Zend\Log\Logger' => function(){
			$logger = new Zend\Log\Logger;
			$writer = new Zend\Log\Writer\Syslog();
			if (!endsWith($_ENV['DEP_NAME'], '/default')) {
				$writer->addFilter(Zend\Log\Logger::ERR);
			}
			$logger->addWriter($writer);
			return $logger;
		}
	),
	'aliases' => array(
		'db' => 'Zend\Db\Adapter\Adapter'
	)
);

return $config;
