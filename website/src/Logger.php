<?php
class Logger {
  static function log($msg) {
    print "Log: " . htmlspecialchars($msg) . "<br />";
  }
}
