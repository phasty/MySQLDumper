<?php
namespace Phasty\MySQLDumper\Operations\Restore {
    use \Phasty\Log\File as log;
    use \Phasty\MySQLDumper\Executor;
    class Data extends Common {
        public function restore($infile, $options) {
            $table = pathinfo($infile, PATHINFO_FILENAME);
            $sql = [
                "SET FOREIGN_KEY_CHECKS=0",
                "ALTER TABLE $table DISABLE KEYS",
                "LOAD DATA INFILE '$infile' INTO TABLE $table CHARACTER SET UTF8 FIELDS TERMINATED BY '\t' ENCLOSED BY '\"'",
                "ALTER TABLE $table ENABLE KEYS",
            ];
            $sql = escapeshellarg(implode(";", $sql));
            log::notice($sql);
            try {
                Executor::execute("mysql -e $sql", $options);
            } catch (\Exception $e) {
                $this->trigger("error", (object)[ "error" => $e->getMessage(), "table" => $table ]);
                return;
            }
            $this->trigger("complete", (object)compact("table"));
        }
    }
}
