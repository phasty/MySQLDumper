<?php
namespace Phasty\MySQLDumper\Operations {
    use \Phasty\Process\Child\Controller as Process;
    use \Phasty\MySQLDumper\Executor;
    class Restore extends Operation {
        /**
         * -h host
         * -u user
         * -d db
         * -P port
         * -p password
         * -c process count
         * -t target dir
         * -i ignore tables
         * --config Config file
         */
        public function __invoke(array $argv) {
            $options = getopt("h:u:d:p:P:c:t:i:o:", [
                "config:"
            ]);
            if (!empty($options[ "config" ])) {
                $configFile = $options[ "config" ];
                if (!file_exists($configFile)) {
                    throw new \Exception("Config file not found: $configFile");
                }
                $options = require $configFile;
                if (!is_array($options)) {
                    throw new \Exception("Wrong config file format");
                }
            }

            if (!isset($options[ "u" ]) ||
                !isset($options[ "d" ])
            ) {
                self::usage($argv);
                exit;
            }
            $options += [
                "h" => "localhost",
                "P" => "3306",
                "c" => 4,
                "t" => "./",
            ];
            $options[ "t" ] = realpath($options[ "t" ]);
            $this->restoreStructure($options);
            $files = $this->getFilesList($options);
            $this->restoreData($files, $options);
        }

        protected function getFilesList($options) {
            $files = glob($options[ "t" ] . "/*.csv");
            usort($files, function($fileA, $fileB) {
                return filesize($fileB) - filesize($fileA);
            });
            return $files;
        }

        public function restoreStructure($options) {
            echo "Restoring structure...\n";
            $streamSet = \Phasty\Stream\StreamSet::instance();
            $structureRestorer = new Process(new Restore\Structure());
            $structureRestorer->on("complete", function() use($streamSet) {
                echo "Structure restored\n";
                $streamSet->stop();
            });
            $structureRestorer->on("error", function($event) use($streamSet) {
                echo "Error during structure restoring: ", var_export($event->getData(), 1), "\n" ;
                exit;
            });
            $structureRestorer->restore($options);
            $streamSet->listen();
        }

        public function restoreData($filesList, $options) {
            echo "Restoring data\n";
            $streamSet = \Phasty\Stream\StreamSet::instance();
            $runNextRestorer = null;
            $onError = function($event, $restorer) use ($streamSet, &$runNextRestorer) {
                $data = $event->getData();
                echo "Error restoring {$data->table}: {$data->error}\n";
                $runNextRestorer();
            };
            $onComplete = function($event) use (&$runNextRestorer) {
                echo $event->getData()->table, " restored\n";
                $runNextRestorer();
            };
            $runNextRestorer = function() use (&$filesList, $onComplete, $options, $onError) {
                if (empty($filesList)) {
                    return;
                }
                $file = array_shift($filesList);
                $restorer = new Process(new Restore\Data());
                $restorer->on("complete", $onComplete)->on("error", $onError);
                echo "Restoring $file...\n";
                $restorer->restore($file, $options);
            };
            for ($i = 0; $i < $options[ "c" ]; $i++) {
                $runNextRestorer();
            }
            $streamSet->listen();
        }
        static public function usage($argv) {
            echo "{$argv[0]} -o restore -u user -d database [ -h host -p port -P password ]\nOr\n{$argv[0]} -o restore --config config.php";
        }
    }
}
