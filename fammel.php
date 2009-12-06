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
     global $LINE;
     
     $tok = new Tokeniser($input);
           
     $parser = new parse_engine($this->_haml);
     
     $parser->reset();
     
     $tokens = $tok->get_all_tokens();
     $tokens = array_merge(array(new Token('INDENT', 0)), $tokens);
     
     foreach($tokens as $t) 
     {
       $LINE = $t->line();
       $parser->eat($t->type(), $t->value());
     }
     
     $parser->eat_eof();
   }
}

?>
