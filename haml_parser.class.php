<?php

class HamlParser extends lime_parser
{
   protected $_cur_tag;
   protected $_cur_attr;
   
   protected $_prev_indent;
   protected $_indent_stack;
   
   protected $_rendered;
   
   function __construct()
   {
      $this->_prev_indent = -1;
      $this->_indent_stack = array();
      
      $this->_rendered = '';
   }
   
   function fetch()
   {
      return $this->_rendered . "\n";
   }
   
   function unwind_indent_stack()
   {
      echo "Unwinding from indent $this->_prev_indent\n";
      print_r($this->_indent_stack);
      
     // $this->check_exec_indenting($this->_prev_indent);
      
      while($this->_prev_indent > 0)
      {
         $this->indent_pop();
      }
   }
   
   function indent_push($token, $indent = false)
   {
      if($indent === false)
      {
         $indent = $this->_prev_indent;
      }
      
      echo "Pushed: $token to $indent\n";
      
      array_push($this->_indent_stack, array($indent, $token));
   }
   
   function indent_pop()
   {      
      list($popped_indent, $popped) = array_pop($this->_indent_stack);
      
      echo "Popped: $popped (from $popped_indent)\n";      
      
      if($popped == '') die();
      
      $this->_prev_indent = $popped_indent;
      $this->render_indent();

      if($popped == '}')
      {
         $this->_rendered .= "<? } ?>\n";
      }
      else
      {
         $this->_rendered .= "</$popped>\n";
      
      }
      
      return $popped_indent;
   }
   
   function check_exec_indenting($indent)
   {
      if($this->_just_did_exec)
      {
         echo "Processing potential code block ($this->_just_did_exec)\n";
         
         if($indent > $this->_prev_indent)
         {
            $this->_rendered .= " { ?>\n";
            $this->indent_push("}");
         }
         else
         {
            $this->_rendered .= " ?>\n";
         }
         
         $this->_just_did_exec = '';
      }
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

   function process_indent($indent, $code = '')
   {      
      $this->check_exec_indenting($indent);
      
      echo "Indent: was $this->_prev_indent, now $indent\n";
                                      
      if($this->_prev_indent < $indent)
      {         
         $this->_prev_indent = $indent;
         
         if(!$code)
         {
            $this->indent_push($this->_cur_tag);
         }
      }
      elseif($this->_prev_indent > $indent)
      {
         while($indent != $popped_indent)
         {
            $popped_indent = $this->indent_pop();
         }
      }
      
      echo "Indent is now $this->_prev_indent\n";
   }
   
   function process_content_line($content)
   {
      if($this->_cur_tag)
      {
         $this->render_indent();
         $this->render_tag(); 
      }

      if($content)
      {
         $this->render_indent();
         $this->_rendered .=  "  $content\n";
      }
      
      $this->_cur_tag = '';
      $this->_cur_attr = array();
   }
   
   function process_echo_line($code)
   {      
      if($this->_cur_tag)
      {
         $this->render_indent();
         $this->render_tag(); 
      }

      if($code)
      {
         $this->render_indent();
         $this->_rendered .=  "<"."?= $code ?>\n";
      }
      
      $this->_cur_tag = '';
      $this->_cur_attr = array();
   }
   
   function process_exec_line($code)
   {
      $this->render_indent();
      $this->_rendered .=  "<" . "? $code";
      
      $this->_cur_tag = '';
      $this->_cur_attr = array();
      $this->_just_did_exec = $code;
   }

   function render_indent()
   {
      $indent = '';
      
      for($i = 0; $i < $this->_prev_indent; $i++)
      {
         $this->_rendered .= " ";
      }
   }
   
   function render_tag()
   {
      if($this->_cur_tag)
      {
         $this->_rendered .= "<$this->_cur_tag";

         asort($this->_cur_attr);
         foreach($this->_cur_attr as $name => $value)
         {
            $this->_rendered .= " $name=$value";
         }
         
         $this->_rendered .= ">\n"; 
      }
   }
}

?>
