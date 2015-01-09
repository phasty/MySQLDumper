<?php
use \Phasty\MySQLDumper;
include __DIR__ . "/../vendor/autoload.php";
/**
 * -o operation
 */
$options = getopt("o:");
if (!isset($options[ "o" ])) {
    usage($argv);
    exit;
}
$operation = MySQLDumper\Factory::getOperation($options[ "o" ]);
$operation($argv);

function usage($argv) {
    echo "{$argv[0]} -o operation\n";
}
