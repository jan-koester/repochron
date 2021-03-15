<?php

namespace App\Http\Controllers\repochron;

use App\Http\Controllers\Controller;
use Response;

class MainController extends Controller {

    // No version is is called -> redirect to current file
    public function redirect ($dir, $file) {
        return redirect('/storage/'.$dir.'/'.$file);
    }

    public function showLog ($dir, $file) {
        $handler = new MainHandler;
        $path = $dir.'/'.$file;

        // Check if file exists
        $handler -> checkExistence($path);

        // Get Git Log
        $log = $handler -> getGitLog($path);
        if (isset($log['error'])) {
            $error = $log['error'];
            return Response::json(['ERROR' => $error], empty($error['code']) ? 500 : $error['code']);
        }
        else {
            return Response::json($handler -> regularResponse($path, 'log', $log), 200);
        }
    }

    public function showVersion ($dir, $file, $id) {
        $handler = new MainHandler;
        $path = $dir.'/'.$file;

        // Check if file exists
        $handler -> checkExistence($path);

        // ID is Date
        if (strpos($id, '-') !== false) {
            $id = trim(str_replace('_', ' ', $id));

            if (
                // Check if $id is date
                preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', $id)
                ||
                // Check if $id is timestamp
                preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1]) (2[0-3]|[01][0-9]):[0-5][0-9]:[0-5][0-9]$/', $id)
            ) {
                $log = $handler -> getGitLog($path);
                if (isset($log['error'])) { $error = $log['error']; }

                else {
                    // Look for a date smaller or equal to given date
                    foreach ($log as $commit) {
                        if ($id >= $commit['date']) {
                            $revision = $commit['revision'];
                            break;
                        }
                    };

                    // Given date is older than version 1 -> throw an error
                    if (!isset($revision)) {
                        $oldest = end($log);
                        $error = [
                            'code'      => 404,
                            'message'   => 'The requested file "'.$path.'" did not yet exist at the given date ('.$id.'). The oldest version is dated '.$oldest['date'].'.',
                            'link'      => $handler -> apiLink($file, $oldest['short']),
                            'versions'  => $log
                        ];
                    }
                }
            }

            // Given ID is neither date nor hash
            else {
                $error = [
                    'code'      => 404,
                    'message'   => 'The given identifier "'.$id.'" is invalid: neither date (YYYY-MM-DD) nor revision hash.'
                ];
            }
        }

        // ID is Hash
        else {
            $log = $handler -> getGitLog($path);
            if (isset($log['error'])) { $error = $log['error']; }

            else {
                // Look for a revision matching the given hash
                foreach ($log as $commit) {
                    if ($id === substr($commit['revision'], 0, strlen($id))) {
                        $revision = $commit['revision'];
                        break;
                    }
                };

                // Given hash does not match any known revision -> throw an error
                if (!isset($revision)) {
                    $error = [
                        'code'      => 404,
                        'message'   => 'There is no revision for the requested file "'.$path.'" matching the given Hash "'.$id.'".',
                        'versions'  => $log
                    ];
                }
            }
        }

        if (!isset($error)) {
            // Get file content
            $content = $handler -> getFileContent ($path, $revision);

            // Extract file format
            $extension = explode('.', $file);
            $extension = strtolower(array_pop($extension));
            if ($extension === 'json') { $content = json_decode($content, true); }
        }

        if (isset($error)) {
            return Response::json(['ERROR' => $error], empty($error['code']) ? 500 : $error['code']);
        }
        else {
            return Response::json($handler -> regularResponse($path, $revision, $content), 200);
        }
    }
}

class MainHandler {

    public function checkExistence ($path) {
        if (!file_exists(config('repochron.path.data').'/'.$path)) { die (abort(404)); }
    }

    public function getGitLog ($path) {
        $raw = shell_exec(implode(' ', [
            'git -C',
            config('repochron.path.data'),
            'log',
            '--pretty=\'format:%H||%cd\'',
            '--date=format:\'%Y-%m-%d %H:%M:%S\'',
            $path
        ]));

        if (empty($raw)) {
            return ['error' => [
                'code'      => 500,
                'message'   => 'The requested file "'.$path.'" exists, but the logs are empty.'
            ]];
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
                    'link'      => $this -> apiLink($path, $hash_short)
                ];
            }
            return $log;
        }
    }

    public function getFileContent ($path, $revision) {

        $content = shell_exec(implode(' ', [
            'git -C',
            config('repochron.path.data'),
            'show',
            $revision.':'.$path
        ]));

        if (empty($content)) {
            return null;
        }
        else {
            return trim($content);
        }
    }

    public function apiLink ($path, $revision) {
        return env('APP_URL').'/api/'.$path.'::'.$revision;
    }

    public function regularResponse ($path, $revision, $contents) {
        return [
            'meta'      => config('repochron.meta'),
            'self'      => $this -> apiLink($path, $revision),
            'partOf'    => $revision === 'log' ? null : $this -> apiLink($path, 'log'),
            'kind'      => $revision === 'log' ? 'single file version index' : 'single file content',
            'contents'  => $contents
        ];
    }
}
