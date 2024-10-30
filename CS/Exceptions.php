<?php
// Exceptions used with CloudSwipe Toolkit

class CS_Exception extends Exception {
  public function get_message() {
    return parent::getMessage();
  }
}

class CS_Exception_API extends CS_Exception {}
class CS_Exception_API_InvalidPublicKey extends CS_Exception_API {}
class CS_Exception_API_InvalidSecretKey extends CS_Exception_API {}

class CS_Exception_Store extends CS_Exception {}
class CS_Exception_Store_ReceiptNotFound extends CS_Exception_Store {}

class CS_Exception_Product extends CS_Exception {}
