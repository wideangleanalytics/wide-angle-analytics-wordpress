<?php
class WideAngleValidated {

  private $name;
  private $value;
  private $normalized;
  private $error;
  private $isValid;

  private function __construct($name, $value, $normalized, $error, $isValid) {
    $this->name = $name;
    $this->value = $value;
    $this->normalized = $normalized;
    $this->error = $error;
    $this->isValid = $isValid;
  }

  public static function createValid($name, $value, $normalized) {
    return new self($name, $value, $normalized, null, true);
  }

  public static function createInvalid($name, $value, $error) {
    return new self($name, $value, null, $error, false);
  }

  public function get_name() {
    return $this->name;
  }

  public function get_value() {
    if($this->normalized !== null) {
      return $this->normalized;
    }
    return $this->value;
  }

  public function get_error() {
    if(!$this->isValid) {
      return $this->error;
    }
    return null;
  }

  public function is_valid() {
    return $this->isValid;
  }

}
?>