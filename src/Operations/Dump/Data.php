<?php
namespace Phasty\MySQLDumper\Operations\Dump {
    use \Phasty\Log\File as log;
    use \Phasty\MySQLDumper\Executor;
    class Data extends Common {
        public function dump($table, $options) {
            $outfile = "{$options['t']}/$table.csv";
            $sql = escapeshellarg("SELECT * INTO OUTFILE '$outfile' FIELDS TERMINATED BY '\t' ENCLOSED BY '\"' FROM $table");
            log::notice($sql);
            Executor::execute("mysql -e $sql", $options);
            $this->trigger("complete");
        }
    }
}
