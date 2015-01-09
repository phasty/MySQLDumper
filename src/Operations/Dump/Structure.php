<?php
namespace Phasty\MySQLDumper\Operations\Dump {
    use \Phasty\Log\File as log;
    use \Phasty\MySQLDumper\Executor;
    class Structure extends Common {
        public function dump($options) {
            $targetDir = $options[ "t" ];
            Executor::execute("mysqldump --add-drop-database --add-drop-table --allow-keywords --skip-comments --no-data --result-file $targetDir/structure.sql", $options);
            $this->trigger("complete");
        }
    }
}
