<?php

include "../tokeniser.class.php";

class DescribeTokeniser extends PHPSpec_Context
{
  function it_should_fetch_the_next_character()
  {
    $tok = new Tokeniser("abc");
    
    $this->spec($tok->get_char())->should->equal('a');
    $this->spec($tok->get_char())->should->equal('b');
    $this->spec($tok->get_char())->should->equal('c');
    $this->spec($tok->get_char())->should->equal('');
  }
  
  function it_should_go_back_by_one_character()
  {
    $tok = new Tokeniser("abc");
    
    $this->spec($tok->get_char())->should->equal('a');
    $tok->rewind();
    $this->spec($tok->get_char())->should->equal('a');
  }
  
  function it_should_do_nothing_if_at_the_start_of_input()
  {
    $tok = new Tokeniser("abc");
    
    $tok->rewind();
    $this->spec($tok->get_char())->should->equal('a');
  }
  
  function it_should_skip_spaces()
  {
    $tok = new Tokeniser("   a");
    
    $tok->skip_whitespace();
    $this->spec($tok->get_char())->should->equal('a');
  }
  
  function it_should_not_skip_non_spaces()
  {
    $tok = new Tokeniser("a   b");
    
    $tok->skip_whitespace();
    $this->spec($tok->get_char())->should->equal('a');
  }
  
  function it_should_return_a_name()
  {
    $tok = new Tokeniser("abcd e");

    $this->spec($tok->get_name())->should->equal('abcd');
    $this->spec($tok->get_char())->should->equal(' ');
    
    $tok = new Tokeniser("abcd.e");

    $this->spec($tok->get_name())->should->equal('abcd');
    $this->spec($tok->get_char())->should->equal('.');
    
    $tok = new Tokeniser("abcd#e");

    $this->spec($tok->get_name())->should->equal('abcd');
    $this->spec($tok->get_char())->should->equal('#');
  }
  
  function it_should_return_the_rest_of_the_line()
  {
     $tok = new Tokeniser("foo\nbar");
     
     $this->spec($tok->get_line(''))->should->equal('foo');
     $this->spec($tok->get_line(''))->should->equal('bar');
     
     $tok = new Tokeniser("foo\nbar");
     
     $tok->get_char();     
     $this->spec($tok->get_line(''))->should->equal('oo');
  }
  
  function it_should_return_bangs()
  {
     $tok = new Tokeniser("!!!");
     
     $this->spec($tok->get_doctype(''))->should->equal('!!!');
     
     $tok = new Tokeniser("!!! !");
     
     $this->spec($tok->get_doctype(''))->should->equal('!!!');
       $this->spec($tok->get_char())->should->equal(' ');
  }
  
  function it_should_return_number_of_spaces()
  {
     $tok = new Tokeniser("    a");
     
     $this->spec($tok->get_indent())->should->equal(4);
     $this->spec($tok->get_char())->should->equal('a');
  }
  
  function it_should_get_everything_up_to_comma_or_closing_brace()
  {
     $tok = new Tokeniser("abc,def}");
     
     $this->spec($tok->get_attr_value(''))->should->equal('abc');
     $this->spec($tok->get_char())->should->equal(',');
     $this->spec($tok->get_attr_value(''))->should->equal('def');
     $this->spec($tok->get_char())->should->equal('}');
  }
  
  function it_should_return_an_attribute_name()
  {
    $tok = new Tokeniser("abcd-e_fg");

    $this->spec($tok->get_attr_name(''))->should->equal('abcd-e');
    $this->spec($tok->get_char())->should->equal('_');
  }
  
  function it_should_track_lines_and_columns()
  {
     $tok = new Tokeniser("abc\ndef");
     
     
     //
     // Starting values
     //
     
     $this->spec($tok->line())->should->equal(1);
     $this->spec($tok->column())->should->equal(0);
     
     
     //
     // Rewinding at the start of the file should do nothing
     //
     
     $tok->rewind();
     $this->spec($tok->line())->should->equal(1);
     $this->spec($tok->column())->should->equal(0);
     
     
     //
     // Run through the first line
     //
     
     $tok->get_char();
     $this->spec($tok->line())->should->equal(1);
     $this->spec($tok->column())->should->equal(1);
     
     $tok->get_char();
     $this->spec($tok->line())->should->equal(1);
     $this->spec($tok->column())->should->equal(2);
     
     $tok->get_char();
     $this->spec($tok->line())->should->equal(1);
     $this->spec($tok->column())->should->equal(3);
     
     $tok->get_char();    
     $this->spec($tok->line())->should->equal(2);
     $this->spec($tok->column())->should->equal(1);
     
     
     //
     // Rewinding over a line return should maintain the right values
     //
     $tok->rewind(); 
     $this->spec($tok->line())->should->equal(1);
     $this->spec($tok->column())->should->equal(3);
     
     $tok->get_char();    
     $this->spec($tok->line())->should->equal(2);
     $this->spec($tok->column())->should->equal(1);
     
     
     //
     // Step into line 2
     //
     
     $tok->get_char();    
     $this->spec($tok->line())->should->equal(2);
     $this->spec($tok->column())->should->equal(2);
     
     
     
     //
     // Rewinding in the middle of a line should maintain the right values
     //
     
     $tok->rewind();
     $this->spec($tok->line())->should->equal(2);
     $this->spec($tok->column())->should->equal(1);
  }
  
  function it_should_return_a_token()
  {
     $tok = new Tokeniser("%tag .class #id :attr-name");
     
     $tag = $tok->get_token();
     $class = $tok->get_token();
     $id = $tok->get_token();
     $attr = $tok->get_token();
     
     $this->spec($tag)->should->beAnInstanceOf('Token');
     $this->spec($tag->type())->should->equal('TAG');
     $this->spec($tag->value())->should->equal('tag');
     
     $this->spec($class)->should->beAnInstanceOf('Token');
     $this->spec($class->type())->should->equal('CLASS');
     $this->spec($class->value())->should->equal('class');
     
     $this->spec($id)->should->beAnInstanceOf('Token');
     $this->spec($id->type())->should->equal('ID');
     $this->spec($id->value())->should->equal('id');
     
     $this->spec($attr)->should->beAnInstanceOf('Token');
     $this->spec($attr->type())->should->equal('ATTR_NAME');
     $this->spec($attr->value())->should->equal('attr-name');
  }
  
  function it_should_return_an_array_of_tokens()
  {
     $tok = new Tokeniser("%tag .class #id :attr-name");
     
     $tokens = $tok->get_all_tokens();
     
     $this->spec(is_array($tokens))->should->beTrue();
     $this->spec(count($tokens))->should->equal(4);
  }
}

?>
