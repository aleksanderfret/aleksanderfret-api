<?php

namespace App;

class Logger {
  private $time;
  private $logsPath;

  public function __construct() {
    $this->time = time();
    $this->logsPath = dirname(__DIR__) .  DIRECTORY_SEPARATOR . 'Logs' . DIRECTORY_SEPARATOR;
  }

  public function saveLog($exception, $data) {
    file_put_contents(
      $this->setLogName('api', $this->time, 'log', 'html'),
      print_r($this->setLogContent($exception, $data), true)
    );
  }

  private function setLogName($name, $prefix = '', $suffix = '', $extension = 'html') {
    $fileName = $prefix ? $prefix . '-' . $name : $name;
    $fileName .= $suffix ? '-' . $suffix . '.' . $extension : '.' . $extension;

    return $this->logsPath . $fileName;
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
    return '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8" /></head><body>'
      . '<h1>' . date("Y-m-d H:i:s", $this->time) . '</h1>'
      . '<h2>' . $this->setMessage($exception) . '</h2>'
      . '<div></div><pre>' . print_r($data, true) . '</pre></div>'
      . '</body></html>';
  }
}




