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
        $data_repository = config('repochron.path.data');

        // Check if file exists
        if (!file_exists($data_repository.'/'.$path.'/'.$file)) {
            die (abort(404));
        }

        // Get Handler
        $handler = new MainHandler;
        $full_file = $path.'/'.$file;

        // Get Git Log
        $log = $handler -> getGitLog($full_file);
        return Response::json($log, 200);
    }

    public function version ($path, $file) {
        return $path.' '.$file;
    }
}

class MainHandler {

    public function getGitLog ($file) {
        $raw = shell_exec(implode(' ', [
            'git -C',
            config('repochron.path.data'),
            'log',
            '--pretty=\'format:%H||%h||%cd\'',
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

                $log[] = [
                    'version'       => 'v'.($versions - $i),
                    'date'          => $commit[2],
                    'hash_short'    => $commit[1],
                    'hash_long'     => $commit[0],
                    'link'          => env('APP_URL').'/api/'.$file.'/'.$commit[1]
                ];
            }
            return $log;
        }
    }
}
