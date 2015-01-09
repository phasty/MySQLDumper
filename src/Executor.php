<?php
namespace Phasty\MySQLDumper {
    class Executor {
        static public function execute($cmd, $options, $tail = "") {
            $host     = escapeshellarg($options['h']);
            $database = escapeshellarg($options['d']);
            $user     = escapeshellarg($options['u']);
            $port     = intval($options['P']);
            $cmd .= " --host $host --database $database --user $user --port $port";
            if (isset($options[ "p" ])) {
                $cmd .= " -p" . escapeshellarg($options['p']);
            }
            $cmd .= " $tail";
            exec($cmd, $output, $return);
            \Phasty\Log\File::error($cmd);
            if ($return) {
                throw new \Exception("Command executed with code $return: $cmd");
            }
            return $output;
        }
    }
}
