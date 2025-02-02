<?php

class CS_Common {

  /**
   * Return true if the provided array is an associative array.
   *
   * @param array $array The array to inspect
   * @return boolean True if array is assoc
   */
  public static function is_assoc($array) {
    return is_array($array) && !is_numeric(implode('', array_keys($array)));
  }

  public static function unavailable_product_data() {
    $product_data = array(
      array('id' => 0, 'sku' => '', 'price' => '', 'name' => 'Products Unavailable')
    );
    return $product_data;
  }

  /**
	 * Return the scrubbed value from the source array for the given key.
	 * If the given $key is not in the source array, return NULL
	 * If the source parameter is not provided, use the $_REQUEST array
	 *
	 * This function uses scrub_value() to remove the following characters:
	 * < > \ : ; `
	 *
	 * Pass in the type 'int' to cast the returned value to an integer
	 *
	 * @param string $key
	 * @param array (Optional) $source
	 * @return mixed
	 */
	public static function scrub($key, $source=null, $type=null) {
	  // Set $source to $_REQUEST global if not defined
	  if(!isset($source)) {
	    $source = $_REQUEST;
	  }

    $value = null;
    if(isset($source[$key])) {
      $value = self::deep_clean($source[$key]);
    }

    if(isset($type)) {
      if($type == 'int') {
        $value = (int)$value;
      }
    }

    return $value;
  }

  public static function deep_clean(&$data) {
    if(is_array($data)) {
      foreach($data as $key => $value) {
        if(is_array($value)) {
          $data[$key] = self::deep_clean($value);
        }
        else {
          $value = strip_tags($value);
          $data[$key] = self::scrub_value($value);
        }
      }
    }
    else {
      $data= strip_tags($data);
      $data = self::scrub_value($data);
    }
    return $data;
  }

  /**
   * Remove the following characters: < > \ : ; `
   */
  private static function scrub_value($value) {
    $value = preg_replace('/[<>\\\\:;`]/', '', $value);
    return $value;
  }

  /**
   * Return a random string that contains only numbers or uppercase letters or
   * for added entropy, lowercase letters and symbols.
   *
   * The default length of the string is 14 characters.
   *
   * @param int (Optional) $length The number of characters in the string. Default: 14
   * @param boolean (Optional) $entropy If true, included lowercase letters and symbols in the string
   * @return string
   */
  public static function rand_string($length = 14, $entropy=false) {
    $string = '';
    $chrs = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    if($entropy) {
      $chrs .= 'abcdefghijklmnopqrstuvwxyz!@#%^&*()+~:';
    }
    for($i=0; $i<$length; $i++) {
      $loc = mt_rand(0, strlen($chrs)-1);
      $string .= $chrs[$loc];
    }
    return $string;
  }
}
