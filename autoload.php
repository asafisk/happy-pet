<?php
/**
 * Basic PSR-4 autoloader
 */
class Autoloader
{
    public static function register()
    {
        spl_autoload_register(function($class)
        {
            $file = __DIR__ . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
            if (file_exists($file)) {
                include $file;
                return true;
            }
            return false;
        });
    }
}
Autoloader::register();

/**
 * Global exception handler
 */
function just_in_case($exception) {
    echo 'Exception: ' . $exception->getMessage() . '<br>';
    echo 'Thrown in: ' . $exception->getFile() . '<br>';
    echo 'Line: ' . $exception->getLine() . '<br>';
    echo '<small>' . $exception->getTraceAsString() . '</small>';
}
set_exception_handler('just_in_case');
?>