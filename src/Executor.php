<?php
namespace Phasty\MySQLDumper {
    class Executor {
        static public function execute($cmd, $options, $tail = "") {
            if (isset($options['h'])) {
                $cmd .= " --host " . escapeshellarg($options['h']);
            }
            if (isset($options['u'])) {
                $cmd .= " --user " . escapeshellarg($options['u']);
            }
            if (isset($options['P'])) {
                $cmd .= " --port " . intval($options['P']);
            }
            if (isset($options[ "p" ])) {
                $cmd .= " -p" . escapeshellarg($options['p']);
            }
            if (isset($options['d'])) {
                $cmd .= " " . escapeshellarg($options['d']);
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
