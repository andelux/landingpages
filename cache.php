<?php

if ( ! $_POST ) {
    $hash = md5($_SERVER['REQUEST_URI']);

    // TODO: get others hashes

    $hash_file = __DIR__ . '/var/cache/' . $hash;

    // Exists the cache file?
    if ( is_file($hash_file) ) {
        // And is it still alive?
        $expire_date = strtotime(file_get_contents("{$hash_file}.expire"));
        if ( time() < $expire_date ) {
            // Cache output
            $headers = json_decode(file_get_contents("{$hash_file}.json"), true);
            $config = $headers['LP-Cache-Config'];
            unset($headers['LP-Cache-Config']);

            // Variables
            $variables = array();
            foreach ( explode(',',$config['variables']) as $cache_var ) {
                $variables[$cache_var] = 'cache.'.strtolower($cache_var).'.php';
            }

            // Session
            session_name( $config['session.name'] );
            session_start();

            // Send headers
            header("LP-Cache: {$hash}");
            foreach ( $headers as $name => $value ) {
                header("{$name}: {$value}", true);
            }

            // Send content
            $content = file_get_contents($hash_file);
            foreach ( $variables as $cache_var => $file ) {
                $content = str_replace('{{'.$cache_var.'}}', require($file), $content);
            }
            // TODO: compress?
            echo $content;

            exit();
        } else {
            // Cache expired
            @unlink($hash_file);
        }
    }
}
