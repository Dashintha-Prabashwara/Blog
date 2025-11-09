<?php

class Cache {
    private static $cacheDir = __DIR__ . '/../cache/';
    private static $cacheDuration = 3600; // 1 hour
    
    public static function get($key) {
        $filename = self::$cacheDir . md5($key) . '.cache';
        
        if (file_exists($filename) && (time() - filemtime($filename)) < self::$cacheDuration) {
            return unserialize(file_get_contents($filename));
        }
        
        return false;
    }
    
    public static function set($key, $data) {
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0777, true);
        }
        
        $filename = self::$cacheDir . md5($key) . '.cache';
        return file_put_contents($filename, serialize($data));
    }
    
    public static function clear($key = null) {
        if ($key) {
            $filename = self::$cacheDir . md5($key) . '.cache';
            if (file_exists($filename)) {
                unlink($filename);
            }
        } else {
            array_map('unlink', glob(self::$cacheDir . '*.cache'));
        }
    }
}
