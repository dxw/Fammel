<?php

class FammelIndentExeption extends Exception
{
  public $line;
  
  public function __construct($message, $line)
  {
    parent::__construct($message);
    $this->line = $line;
  }
}

class FammelParseException extends Exception
{
  public $line;
  public $token;
  public $value;
  
  public function __construct($message, $line, $token, $value)
  {
    parent::__construct($message);
    $this->line = $line;
    $this->token = $token;
    $this->value = $value;
  }
}

?>
