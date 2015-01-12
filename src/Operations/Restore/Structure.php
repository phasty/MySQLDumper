<?php
namespace Phasty\MySQLDumper\Operations\Restore {
    use \Phasty\Log\File as log;
    use \Phasty\MySQLDumper\Executor;
    class Structure extends Common {
        public function restore($options) {
            $targetDir = $options[ "t" ];
            $this->recreateDatabase($options);
            Executor::execute("mysql", $options, " < $targetDir/structure.sql");
            $this->trigger("complete");
        }

        protected function recreateDatabase($options) {
            $dropDbConfig = $options;
            unset($dropDbConfig[ "d" ]);
            $dropSql = escapeshellarg("DROP DATABASE IF EXISTS {$options['d']}");
            Executor::execute("mysql -e $dropSql", $dropDbConfig);
            $createSql = escapeshellarg("CREATE DATABASE {$options['d']}");
            Executor::execute("mysql -e $createSql", $dropDbConfig);
        }
    }
}
