<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class setup_storage extends Command {

    protected $signature = 'setup:storage {root_path?}';
    protected $description = 'Create directories, Links and Git-Repo';

    public function __construct() {
        parent::__construct();
    }

    /*private function getParameter($parameter)
    {
        $data = $this->argument($parameter);
        return $data;
    }*/

    // -------------------------------------------------------------------------

    function handle () {

        echo "\n\n------------------------\n"."RepoChron - Storage Setup"."\n-------------------------\n\n";

        // Get given Root Path
        $root_path = $this->argument('root_path');
        if (empty($root_path)) {
            $root_path = '/opt/projects/repochron/';
            echo "No parameter given - set root_path to default: ";
        }
        else {
            echo "Parameter given - set root_path to: ";
        }
        echo $root_path."\n";

        // Create Directory
        $storage_directory = $root_path.(substr($root_path, -1) === '/' ? '' : '/').'data';
        if (intval(shell_exec('[ -d "'.$storage_directory.'" ] && echo 1 || echo 0')) === 1) {
            echo 'Storage Directory '.$storage_directory.' does already exist.'."\n";
        }
        else {
            echo 'Storage Directory '.$storage_directory.' does not exist.'."\n";
            echo 'Trying to create Storage Dirctory ... ';
            mkdir($storage_directory, 0770, TRUE) OR die ($this -> abortScript($storage_directory));
            echo 'SUCCESS'."\n";
        }

        echo "\n------------------------\n"." SUCCESSFULLY FINISHED "."\n-------------------------\n\n";
    }

    function abortScript ($storage_directory) {
        echo "\n".'ERROR > Aborting script'."\n";
        echo 'Trying to rollback ... ';
        rmdir($storage_directory) OR die ('ERROR'."\n");
        echo 'SUCCESS'."\n";

        die("\n------------------------\n"." ERRONEOUSLY TERMINATED "."\n-------------------------\n\n");
    }
}
