<?php

namespace ellsif\WelCMS;

trait Singleton
{
  protected function __construct()
  {
  }

  private function __clone()
  {
  }

  private function __wakeup()
  {
  }

  protected static function instance()
  {
    static $instance = null;
    if ($instance === null) {
      $instance = new self();
    }
    return $instance;
  }
}