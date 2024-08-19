<?php

if (!function_exists('bytes_format')) {
    /**
     * Format bytes to human readable format.
     * 
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    function bytes_format(int $bytes, int $precision = 2): string
    { 
        $units = array('B', 'KB', 'MB', 'GB', 'TB'); 
        
        $bytes = max($bytes, 0); 
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
        $pow = min($pow, count($units) - 1); 
        
        $bytes /= (1 << (10 * $pow)); 
        
        return round($bytes, $precision) . $units[$pow]; 
    }  
}

if (!function_exists('property_isset')) {
    function property_isset(mixed $object, string $property) {
        return property_exists($object, $property)
            && isset($object->{$property})
            && $object->{$property} !== null;
    }
}