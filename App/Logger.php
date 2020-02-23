<?php

namespace App;

class Logger {

  public function saveLog($exception, $data) {
    file_put_contents(
      $this->setLogName('-log.html'),
      print_r($this->setLogContent($exception, $data), true)
    );
  }

  private function setLogName($suffix) {
      return time() . $suffix;
  }

  private function setMessage($e) {
    
    return is_string($e)
      ? $e
      : 'An error '
        . $e->getMessage()
        . 'occurred in '
        . $e->getFile()
        . 'file in '
        . $e->getLine()
        . '.';
  }

  private function setLogContent($exception, $data) {
    return ['MESSAGE' => $this->setMessage($exception), 'CONTENT' => $data];
  }
}
