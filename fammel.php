<?php

include_once dirname(__FILE__) . "/exceptions.php";

include_once dirname(__FILE__) . "/lib/lime/parse_engine.php";
include_once dirname(__FILE__) . "/haml_parser_new.class.php";
include_once dirname(__FILE__) . "/haml.class.php";

include_once dirname(__FILE__) . "/tokeniser.class.php";



class Fammel
{
   protected $_haml;
   protected $_line;
   protected $_file;
   
   function __construct()
   {
      $this->_haml = new Haml();
      $this->_file = null;
   }
   
   function render()
   {
      return $this->_haml->render();
   }
   
   function line()
   {
     return $this->_line;
   }
   
   function parse_file($file)
   {
     $this->_file = $file;
     return $this->parse(file_get_contents($file));
   }
   
   function parse($input)
   {
     try
     {
       global $LINE;
       
       $tok = new Tokeniser($input);
             
       $parser = new parse_engine($this->_haml);
       
       $parser->reset();
       
       $tokens = $tok->get_all_tokens();
       $tokens = array_merge(array(new Token('INDENT', 0)), $tokens);
       
       foreach($tokens as $t) 
       {
         $LINE = $this->_line = $t->line();
         $parser->eat($t->type(), $t->value());
       }
       
       $parser->eat_eof();
  
       return true;
     }
     catch(Exception $e)
     {
       $token = $value = '';
       preg_match('/\((.*?)\)\((.*?)\)/', $e->getMessage(), $matches);
       
       if($matches)
       { 
         $token = $matches[1];
         $value = $matches[2];
         
         $message = "Parse error: unexpected $token ('$value')";
       }
       else
       {
         $message = $e->getMessage();
       }
       
       $message .= " at line $this->_line";
       
       if($this->_file)
       {
         $message .= " in $this->_file";
       }
       
       $message .= "\n";
       
       throw new FammelParseException($message, $this->_line, $token, $value);
     }
   }
}

?>
