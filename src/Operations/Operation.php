<?php
namespace Phasty\MySQLDumper\Operations {
    use \Phasty\Process\Child\Controller as Process;

    abstract class Operation {
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
            $this->copyStructure($options);
            $objects = $this->getObjectsList($options);
            $this->copyData($objects, $options);
        }

        public function copyStructure($options) {
            echo "Copying structure...\n";
            $streamSet = \Phasty\Stream\StreamSet::instance();
            $copier = new Process($this->getStructureCopier());

            $copier->on("complete", function() use($streamSet) {
                echo "Structure copied\n";
                $streamSet->stop();
            });

            $copier->on("error", function($event) use($streamSet) {
                echo "Error during structure copying: ", var_export($event->getData(), 1), "\n";
                exit;
            });

            $copier->copy($options);

            $streamSet->listen();
        }

        protected function copyData($objectsList, $options) {
            echo "Copying data\n";
            $streamSet = \Phasty\Stream\StreamSet::instance();
            $runNextCopier = null;
            $onError = function($event, $copier) use ($streamSet, &$runNextCopier) {
                $data = $event->getData();
                echo "Error copying {$data->table}: {$data->error}\n";
                $runNextCopier();
            };
            $onComplete = function($event) use (&$runNextCopier) {
                echo $event->getData()->table, " copyed\n";
                $runNextCopier();
            };
            $runNextCopier = function() use (&$objectsList, $onComplete, $options, $onError) {
                if (empty($objectsList)) {
                    return;
                }
                $object = array_shift($objectsList);
                $copier = new Process($this->getDataCopier());
                $copier->on("complete", $onComplete)->on("error", $onError);
                echo "Copying $object...\n";
                $copier->copy($object, $options);
            };
            for ($i = 0; $i < $options[ "c" ]; $i++) {
                $runNextCopier();
            }
            $streamSet->listen();
        }
    }
}
