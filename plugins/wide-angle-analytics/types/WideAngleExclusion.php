<?php

class WideAngleExclusion {
  public $type;
  public $value;

  public function __construct($type, $value) {
    $this->type = $type;
    $this->value = $value;
  }

  public function get_type() {
    return $this->type;
  }

  public function get_value() {
    return $this->value;
  }
}

?>