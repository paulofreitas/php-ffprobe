<?php

/*
 * ffprobe class helper for ffmpeg 0.9+ (JSON support)
 * Written by Paulo Freitas <me@paulofreitas.me> under CC BY-SA 3.0 license
 */
class ffprobe
{
    public function __construct($filename, $prettify = false)
    {
        if (!file_exists($filename)) {
            throw new Exception(sprintf('File not exists: %s', $filename));
        }

        $this->__metadata = $this->__probe($filename, $prettify);
    }

    private function __probe($filename, $prettify)
    {
        // Start time
        $init = microtime(true);

        // Default options
        $options = '-loglevel quiet -show_format -show_streams -print_format json';

        if ($prettify) {
            $options .= ' -pretty';
        }

        // Avoid escapeshellarg() issues with UTF-8 filenames
        setlocale(LC_CTYPE, 'en_US.UTF-8');

        // Run the ffprobe, save the JSON output then decode
        $json = json_decode(shell_exec(sprintf('ffprobe %s %s', $options,
            escapeshellarg($filename))));

        if (!isset($json->format)) {
            throw new Exception('Unsupported file type');
        }

        // Save parse time (milliseconds)
        $this->parse_time = round((microtime(true) - $init) * 1000);

        return $json;
    }

    public function __get($key)
    {
        if (isset($this->__metadata->$key)) {
            return $this->__metadata->$key;
        }

        throw new Exception(sprintf('Undefined property: %s', $key));
    }
}
