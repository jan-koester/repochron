<?php

namespace App\Http\Controllers\repochron;

use App\Http\Controllers\Controller;
use Response;

class MainController extends Controller {

    // No version is is called -> redirect to current file
    public function redirect ($path, $file) {
        return redirect('/storage/'.$path.'/'.$file);
    }

    public function log ($path, $file) {
        $handler = new MainHandler;
        $full_file = $path.'/'.$file;

        // Check if file exists
        $handler -> checkExistence($full_file);

        // Get Git Log
        $log = $handler -> getGitLog($full_file);
        return Response::json($log, 200);
    }

    public function version ($path, $file, $identifier) {
        $handler = new MainHandler;
        $full_file = $path.'/'.$file;

        // Check if file exists
        $handler -> checkExistence($full_file);

        // Check identifier
        if (strpos($identifier, '-') === false) {
            $id_is_hash = true;
        }
        else {
            $identifier = trim(str_replace('_', ' ', $identifier));
            if (
                // Check if $id is a date
                preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', $identifier)
                ||
                // Check if $id is a timestamp
                preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1]) (2[0-3]|[01][0-9]):[0-5][0-9]:[0-5][0-9]$/', $identifier)
            ) {
                $id_is_hash = false;
            }
        }

        // Abort if unknown identifier
        if (!isset($id_is_hash)) { die (abort(404)); }

        // Get Git Log
        $log = $handler -> getGitLog($full_file);

        $revisions = array_column($log, 'short');


        return $handler -> getRevision ($full_file, $identifier);
    }
}

class MainHandler {

    public function checkExistence ($file) {
        if (!file_exists(config('repochron.path.data').'/'.$file)) {
            die (abort(404));
        }
    }

    public function getGitLog ($file) {
        $raw = shell_exec(implode(' ', [
            'git -C',
            config('repochron.path.data'),
            'log',
            '--pretty=\'format:%H||%cd\'',
            '--date=format:\'%Y-%m-%d %H:%M:%S\'',
            $file
        ]));

        if (empty($raw)) {
            return [];
        }
        else {
            $raw = explode("\n", $raw);
            $versions = count($raw);

            $log = [];
            foreach($raw as $i => $commit) {

                $commit     = explode("||", $commit);
                $date       = $commit[1];
                $hash_short = substr($commit[0], 0, 10);
                $hash_long  = $commit[0];

                $log[] = [
                    'version'   => 'v'.($versions - $i),
                    'date'      => $date,
                    'revision'  => $hash_long,
                    'short'     => $hash_short,
                    'link'      => env('APP_URL').'/api/'.$file.'/'.$hash_short
                ];
            }
            return $log;
        }
    }

    public function getRevision ($file, $revision) {

        $content = shell_exec(implode(' ', [
            'git -C',
            config('repochron.path.data'),
            'show',
            $revision.':'.$file
        ]));

        if (empty($content)) {
            die (abort(404));
        }
        else {
            return trim($content);
        }
    }
}
