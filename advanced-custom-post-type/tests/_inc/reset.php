<?php

include 'bootstrap.php';

use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Schema_Manager;

/**
 * Reset ACPT schema
 */
function resetSchema()
{
    ACPT_Schema_Manager::down();
	$old_version = get_option('acpt_version', 0);
	ACPT_DB::createSchema(ACPT_PLUGIN_VERSION, get_option('acpt_current_version') ?? oldACPTPluginVersion($old_version));
    ACPT_DB::sync();
}

echo '***********************************************' . PHP_EOL;
echo '* RESETTING DB                                *' . PHP_EOL;
echo '***********************************************' . PHP_EOL;

try {
    resetSchema();
    echo "Done!";
} catch (\Exception $exception){
    echo "ERROR: " . $exception->getMessage();
}

echo PHP_EOL;
echo PHP_EOL;