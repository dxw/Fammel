<?php

class DescribeParser extends PHPSpec_Context
{
  function parse_test_file($file, $expect_line)
  {
    $fammel = new Fammel();

    try
    {
      $fammel->parse(file_get_contents($file));
    }
    catch(Exception $e)
    {
      $this->spec($e)->should->beAnInstanceOf(FammelParseException);
      $this->spec($e->line)->should->equal($expect_line);
      return $e;
    }

    $this->fail();
    return false;
  }

  function it_should_fail_on_no_closing_brace()
  {
    $e = $this->parse_test_file(dirname(__FILE__) . '/data/no_attr_closing_brace.haml', 1);
    
    $this->spec($e->token)->should->equal('LINE_CONTENT');
  }

  function it_should_fail_on_no_open_brace()
  {
    $e = $this->parse_test_file(dirname(__FILE__) . '/data/no_attr_open_brace.haml', 1);
    
    $this->spec($e->token)->should->equal('ATTR_NAME');
  }
  
  function it_should_fail_on_no_closing_quote()
  {
    $e = $this->parse_test_file(dirname(__FILE__) . '/data/no_attr_closing_quote.haml', 1);
    
    $this->spec($e->token)->should->equal('LINE_CONTENT');
  }

  function it_should_fail_on_no_open_quote()
  {
    $e = $this->parse_test_file(dirname(__FILE__) . '/data/no_attr_open_quote.haml', 1);
    
    $e = $this->spec($e->token)->should->equal('LINE_CONTENT');
  }
  
  function it_should_fail_on_no_colon()
  {
    $e = $this->parse_test_file(dirname(__FILE__) . '/data/no_attr_colon.haml', 1);
    
    $e = $this->spec($e->token)->should->equal('LINE_CONTENT');
  }

  function it_should_fail_on_no_sep()
  {
    $e = $this->parse_test_file(dirname(__FILE__) . '/data/no_attr_sep.haml', 1);
    
    $this->spec($e->token)->should->equal('ATTR_NAME');
  }
   
}
