<?php

class Apitter
{

    private static $apt;

    public static function getInstance()
    {
        if (self::$apt == null) {
            self::$apt = new Apitter();
        }
        return self::$apt;
    }

    private function run_apt($def, $arg)
    {
        $url = 'https://apitter.ir/api/v1/' . _APT_TOKEN . '/' . $def . '/' . $arg;
        return file_get_contents($url);
    }

    public function get_user_data($arg)
    {
        return $this->run_apt('user', $arg);
    }
}
