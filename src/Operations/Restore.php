<?php
namespace Phasty\MySQLDumper\Operations {
    use \Phasty\Process\Child\Controller as Process;
    use \Phasty\MySQLDumper\Executor;
    class Restore extends Operation {
        protected function getObjectsList($options) {
            $files = glob($options[ "t" ] . "/*.csv");
            usort($files, function($fileA, $fileB) {
                return filesize($fileB) - filesize($fileA);
            });
            return $files;
        }

        protected function getStructureCopier() {
            return new Restore\Structure();
        }

        protected function getDataCopier() {
            return new Restore\Data();
        }
        static public function usage($argv) {
            echo "{$argv[0]} -o restore -u user -d database [ -h host -p port -P password ]\nOr\n{$argv[0]} -o restore --config config.php";
        }
    }
}
