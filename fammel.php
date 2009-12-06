<?php

include_once "lib/lime/parse_engine.php";
include_once "haml_parser_new.class.php";
include_once "haml.class.php";

include_once "tokeniser.class.php";

class Fammel
{
   protected $_haml;
   
   function __construct()
   {
      $this->_haml = new Haml();
   }
   
   function render()
   {
      return $this->_haml->render();
   }
   
   function parse($input)
   {
      $tok = new Tokeniser($input);
            
      $parser = new parse_engine($this->_haml);
      
      try
      {
         $parser->reset();
         
         $tokens = $tok->get_all_tokens();
         $tokens = array_merge(array(new Token('INDENT', 0)), $tokens);
         
         print_r($tokens);
         
         foreach($tokens as $t) 
         {
            $parser->eat($t->type(), $t->value());
         }
         
         $parser->eat_eof();
      } 
      catch(parse_error $e)
      {
         echo $e->getMessage(), "\n";
      }
   }
}

?>
