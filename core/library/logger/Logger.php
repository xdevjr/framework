<?php

namespace core\library\logger;

use core\enums\LogType;

abstract class Logger
{
    public static function makeLogInfile(string $path, string $message, LogType $type): void
    {
        if (!file_exists(dirname($path)))
            mkdir(dirname($path), 0777, true);

        $message = preg_replace("/\R+/", "", $message);
        $message = "{$type->name} | {$message} | " . date("Y-m-d H:i:s") . PHP_EOL;
        file_put_contents($path, $message, FILE_APPEND);
    }

    public static function getLogsFromFile(string $path): array|false
    {
        if (!file_exists($path))
            return false;

        $file = file($path, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
        foreach ($file as $log) {
            [$type, $message, $date] = explode("|", $log);
            $logs[] = ["type" => trim($type), "message" => trim($message), "date" => trim($date)];
        }

        return $logs;
    }
}
