<?php
namespace Phasty\MySQLDumper\Operations\Dump {
    use \Phasty\Log\File as log;
    use \Phasty\MySQLDumper\Executor;
    class Data extends Common {
        public function copy($table, $options) {
            $outfile = "{$options['t']}/$table.csv";
            $sql = escapeshellarg("SELECT * INTO OUTFILE '$outfile' FIELDS TERMINATED BY '\t' ENCLOSED BY '\"' FROM $table");
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
