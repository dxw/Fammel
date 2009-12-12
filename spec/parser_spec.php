<?php

class DescribeParser extends PHPSpec_Context
{
  function parse_test_file($file, $expect_line, $expect_class = 'FammelParseException')
  {
    $fammel = new Fammel();

    try
    {
      $fammel->parse_file($file);
    }
    catch(Exception $e)
    {
      $this->spec($e)->should->beAnInstanceOf($expect_class);
      $this->spec($e->line)->should->equal($expect_line);
      return $e;
    }

    $this->fail();
    return false;
  }
  
  function it_should_succeed_on_all_positive_tests()
  {
    $dir = dirname(__FILE__) .'/data/positive/';
    
    $dh = opendir($dir);
    
    while($file = readdir($dh))
    {
      if($file[0] == '.' || !preg_match('/\.haml$/', $file))
      {
        continue;
      }
      
      $haml = $dir . $file;
      $html = $dir . 'results/' . str_replace(".haml", ".html", $file);
      
      $fammel = new Fammel();
      
      try
      {
        $fammel->parse_file($haml);
      }
      catch(Exception $e)
      {
        // None of these should fail
        $this->fail("Parsing failed: " . $e->getMessage());
      }
      
      try
      {
        $rendered = $fammel->render();
      }
      catch(Exception $e)
      {
        // None of these should fail
        $this->fail('Rendering failed' . $e->getMessage());
      }  
      
      $this->spec($rendered)->should->equal(file_get_contents($html));
    }
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
   
  function it_should_fail_on_inconsistent_indent()
  {
    $e = $this->parse_test_file(dirname(__FILE__) . '/data/fail_on_inconsistent_indent.haml', 3 , 'FammelIndentExeption');
  }
  
  function it_should_fail_on_huge_indent()
  {
    $e = $this->parse_test_file(dirname(__FILE__) . '/data/fail_on_huge_indent.haml', 3 , 'FammelIndentExeption');
  }
}
