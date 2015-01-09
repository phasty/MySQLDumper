<?php
namespace Phasty\MySQLDumper\Operations {
    use \Phasty\Process\Child\Controller as Process;
    use \Phasty\MySQLDumper\Executor;
    class Dump extends Operation {
        /**
         * -h host
         * -u user
         * -d db
         * -P port
         * -p password
         * -c process count
         */
        public function __invoke(array $argv) {
            $options = getopt("h:u:d:p:P:o:c:");
            if (!isset($options[ "u" ]) ||
                !isset($options[ "d" ])
            ) {
                self::usage($argv);
                exit;
            }
            $options += [
                "h" => "localhost",
                "P" => "3306",
                "c" => 4
            ];
            $this->dumpStructure($options);
            $tables = $this->getTablesList($options);
            $this->dumpData($tables, $options);
        }
        protected function getTablesList($options) {
            echo "Getting tables list...";
            $return = Executor::execute("mysql -e 'show tables\G'", $options, "| grep Tables_in | cut -f2 -d' '");
            echo "\nGot tables list\n";
            return $return;
        }
        public function dumpStructure($options) {
            echo "Dumping structure...\n";
            $streamSet = \Phasty\Stream\StreamSet::instance();
            $structureDumper = new Process(new Dump\Structure());
            $structureDumper->on("complete", function() use($streamSet) {
                echo "Structure dumped\n";
                $streamSet->stop();
            });
            $structureDumper->on("error", function($event) use($streamSet) {
                echo "Error during structure dumping: ", var_export($event->getData(), 1), "\n" ;
                exit;
            });
            $structureDumper->dump($options);
            $streamSet->listen();
        }
        public function dumpData($tablesList, $options) {
            echo "Dumping tables\n";
            $streamSet = \Phasty\Stream\StreamSet::instance();
            $runNextDumper = null;
            $onComplete = function($event, $dumper) use	(&$runNextDumper) {
//                echo $dumper->getTableName(), " dumped\n";
                $runNextDumper();
            };
            $onError = function($event, $dumper) use ($streamSet) {
  //              echo $dumper->getTableName(), " error: " , var_export($event, 1), "\n";
            };
            $runNextDumper = function() use (&$tablesList, $options, $onComplete, $onError) {
                if (empty($tablesList)) {
                    echo "Finished dumping";
                    return;
                }
                $table = array_shift($tablesList);
                $dumper = new Process(new Dump\Data());
                $dumper->on("complete", $onComplete)->on("error", $onError);
                echo "Dumping $table\n";
                $dumper->dump($table, $options);
            };
            for ($i = 0; $i < $options[ "c" ]; $i++) {
                $runNextDumper();
            }
            $streamSet->listen();
        }
        static public function usage($argv) {
            echo "{$argv[0]} -o dump -u user -d database [ -h host -p port -P password -i exclude-tables-list -e include-tables-list ]\n";
        }
    }
}
