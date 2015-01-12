<?php
namespace Phasty\MySQLDumper\Operations {

    use \Phasty\MySQLDumper\Executor;
    class Dump extends Operation {
        protected function getObjectsList($options) {
            echo "Getting tables list...";
            $database = $options[ "d" ];
            $where = "";
            if (!empty($options[ "i" ])) {
                $ignore = explode(",", $options[ "i" ]);
                $where = "AND TABLE_NAME NOT LIKE '" . implode("' AND TABLE_NAME NOT LIKE '", $ignore) . "' ";
            }
            $sql = escapeshellarg("SELECT TABLE_NAME FROM information_schema.TABLES WHERE table_schema = \"$database\" $where ORDER BY data_length DESC\G");
            $return = Executor::execute("mysql -e $sql", $options, "| grep TABLE_NAME | cut -f2 -d' '");
            echo "\nGot tables list\n";
            return $return;
        }

        protected function getStructureCopier() {
            return new Dump\Structure();
        }

        protected function getDataCopier() {
            return new Dump\Data();
        }

        static public function usage($argv) {
            echo "{$argv[0]} -o dump -u user -d database [ -h host -p port -P password -i exclude-tables-list -e include-tables-list ]\nOr\n{$argv[0]} -o dump --config config.php\n";
        }
    }
}
