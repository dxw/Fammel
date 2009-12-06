<?php

class HamlRule
{
  const CONTENT = 'content';
  const EXEC_ECHO = 'echo';
  const EXEC = 'exec';
  const ROOT = 'root';
  
  public $indent;
  public $tag;
  public $attr;
  public $action;
  public $content;
  
  public $index;
  public $parent;
  public $children;
  public $next;
  public $prev;
  
  public $next_sibling;
  public $prev_sibling;  
  
  public function __construct($indent, $tag, $attr, $action, $content)
  {
    $this->indent = $indent;
    $this->tag = $tag;
    $this->attr = $attr;
    $this->action = $action;
    $this->content = trim($content);

    $this->parent = $this->next = $this->prev = null; 
    $this->index = 0;
    $this->children = array();
  }
  
  public function init()
  {
   /* if(count($this->children))
    {
      $this->next_sibling = $this->children[count($this->children)-1]->next;
    }
    else
    {
      $this->next_sibling = $this->next;
    }
    */
    
  }
  
  public function render()
  {
    $this->init();
    
    $indent = $rendered = '';
        
    for($i = 0; $i < $this->indent; $i++)
    {      
      $indent .= " ";
    }
      
    if($this->tag)
    {
      $rendered .= "$indent<$this->tag";

      if(count($this->attr))
      {
        asort($this->attr);
        foreach($this->attr as $name => $value)
        {
          $rendered .= " $name=$value";
        }
      }
      
      $rendered .= ">\n";
    }
    
    if($this->content)
    {
      if($this->tag && $this->content)
      {
        $rendered .= "  ";
      }
      
      switch($this->action)
      {
        case HamlRule::CONTENT:   $rendered .= "$indent$this->content"; break;
        case HamlRule::EXEC_ECHO: $rendered .= "$indent<?= $this->content ?>"; break;
        case HamlRule::EXEC:    
          
          if(!($this->prev_sibling->action == HamlRule::EXEC && $this->prev_sibling->next->indent > $this->prev_sibling->indent))
          {
            $rendered .= "$indent<? ";
          }
      
          $rendered .= "$this->content";
          
          if($this->next->indent > $this->indent)
          {
            $rendered .= " {";
          }
          
          $rendered .= " ?>";
          
          break;
      }
      
      $rendered .= "\n";
    }
    
    foreach($this->children as $child)
    {
      $rendered .= $child->render();
    }
    
    if($this->action == HamlRule::EXEC && $this->next->indent > $this->indent)
    {
      $rendered .= "$indent<? } ";
      
      if(!($this->next_sibling->action == HamlRule::EXEC && $this->next_sibling->next->indent > $this->next_sibling->indent))
      {
        $rendered .= " ?>\n";
      }
    }
    
    if($this->tag)
    {
      $rendered .= "$indent</$this->tag>\n";
    }
    
    return $rendered;
  }
}

class HamlParser extends lime_parser
{
  protected $_ast;
  protected $_last_rule;
  
  protected $_cur_attr;
  protected $_cur_tag;
  
  function __construct()
  {
    $this->_cur_attr = $this->_last_parent = array();
    $this->_cur_tag = '';
    
    $this->_ast[0] = $this->_last_rule = new HamlRule(0, '', array(), HamlRule::ROOT, '');
    array_unshift($this->_last_parent, $this->_last_rule);
  }
  
  function add_rule($indent, $tag, $attr, $action, $content)
  {
    if($tag == '' && $content == '')
    { 
      return;
    }
    
    $new_rule = new HamlRule($indent, $tag, $attr, $action, $content);
    $new_rule->index = count($this->_ast);
    
    $this->_ast[] = $new_rule;
    
    $this->_last_rule->next = $new_rule;
    $new_rule->prev = $this->_last_rule;
    
    if($new_rule->indent > $this->_last_rule->indent)
    {
      array_unshift($this->_last_parent, $this->_last_rule);
    }
    else if($new_rule->indent < $this->_last_rule->indent)
    {
      $last_indent = $this->_last_rule->indent;
      
      for(;$last_indent > $indent; $last_indent -= 2)
      {
        $popped = array_shift($this->_last_parent);
      }
      
      $new_rule->prev_sibling = $popped;
      $popped->next_sibling = $new_rule;
    }
    else
    {
      $new_rule->next_sibling = $new_rule->next;
      $new_rule->prev_sibling = $new_rule->prev;
    }
    
    $this->_last_parent[0]->children[] = $new_rule;
    $new_rule->parent = $this->_last_parent[0];
    
    $this->_last_rule = $new_rule;
    $this->_cur_tag = '';
    $this->_cur_attr = array();
  }
  
  function process_tag($tag, $id, $class)
  {
    $this->_cur_tag = $tag;
    
    if($id)
    {
      $this->_cur_attr['id'] = '"' . $id . '"';
    }
    
    if($class)
    {
      $this->_cur_attr['class'] = '"' . $class . '"';
    }
  } 

  function process_attr($name, $value)
  {
    $this->_cur_attr[$name] = $value;
  }
  
  function process_content_rule($indent, $content)
  {
    $this->add_rule($indent, $this->_cur_tag, $this->_cur_attr, HamlRule::CONTENT, $content);
  }
  
  function process_echo_rule($indent, $code)
  {    
    $this->add_rule($indent, $this->_cur_tag, $this->_cur_attr, HamlRule::EXEC_ECHO, $code);
  }
  
  function process_exec_rule($indent, $code)
  {
    $this->add_rule($indent, $this->_cur_tag, $this->_cur_attr, HamlRule::EXEC, $code);
  }
  
  function print_ast()
  {
    foreach($this->_ast as $rule)
    {
      printf("%3.3d\tp=%3.3d,c=%3.3d,n=%3.3d,v=%3.3d\t", $rule->index, $rule->parent->index, $rule->child->index, $rule->next->index, $rule->prev->index); 
      for($i = 0; $i < $rule->indent; $i++) echo " ";
      
      echo "$rule->tag(";
      
      foreach($rule->attr as $attr => $value)
      {
        echo "$attr: $value ";
      }
      
      echo ") $rule->action: $rule->content\n";
    }
  }
  
  function render()
  {
    $this->print_ast();
    
    $this->_rendered = $this->_ast[0]->render();
    
    return $this->_rendered;
  }
}

?>
