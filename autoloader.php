<?php

class CloudSwipe_Loader {
  
  public static function starts_with($haystack, $needle) {
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
  }

  public static function class_loader($class_name) {
    if(self::starts_with($class_name, 'CS_')) {
      $path = str_replace('_', DIRECTORY_SEPARATOR, $class_name);
      $prefix = substr($class_name, 0, 3);
      $root = CS_PATH;
      if(self::starts_with($class_name, 'CS_Exception')) {
        include $root . 'CS/Exceptions.php';
      }
      else {
        include $root . $path . '.php';
      }
    }
  }
  
}

// Register autoloader
spl_autoload_register(array('CloudSwipe_Loader', 'class_loader'));
