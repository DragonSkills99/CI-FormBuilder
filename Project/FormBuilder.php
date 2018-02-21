<?php
if(!class_exists("FormBuilder"))
{
    class FormBuilder{
        
        public function __construct(){
            $this->load = new CI_Loader();
            $this->load->library("form_validation");
            $this->form_validation = new CI_Form_validation();
        }
        
        private $fields = array();
        private $action = "";
        private $method = "GET";
        public function addField($field = null){
            if($field === null) $field = new Field();
            if($field instanceof Field){
                array_push($this->fields, $field);
            }
            return $field;
        }
        
        public function setAction($action){
            $this->action = $action;
            return $this;
        }
        
        public function setMethod($method){
            $this->method = $method;
            return $this;
        }
        
        public function setup(){
            $this->setupChilds($this->fields);
        }
        
        private function setupChilds($fields){
            if(!is_array($fields)) return;
            foreach($fields as $field){
                //$this->form_validation->set_rules('name', 'Number', 'required');
                //echo (isset($field->rule) ? "|ja" : "|nein");
                if(isset($field->rule)){
                    //echo ($field->name."|".$field->description."|".$field->rule).";";
                    $this->form_validation->set_rules($field->name, $field->description, $field->rule);
                }
                $this->setupChilds($field->childs);
            }
        }
        
        public function validate(){
            return $this->form_validation->run() !== FALSE;
        }
        
        public function validation_errors(){
            return $this->validation_errorsChilds($this->fields);
        }
        
        private function validation_error($field){
            $error = $this->validation_errorsChilds(array($field));
            return $error !== null && $error != "" && isset($error) ? $error : false;
        }
        
        private function validation_errorsChilds($fields){
            $ret = "";
            if(!is_array($fields)) return;
            foreach($fields as $field){
                if(isset($field->rule)) {
                    $error = $this->form_validation->error($field->name);
                    if($error !== null && $error != "" && isset($error)){
                        $ret .= "<p>$error</p>";
                    }
                }
                $ret .= $this->validation_errorsChilds($field->childs);
            }
            return $ret;
        }
        
        public function getForm(){
            $action = $this->action;
            $method = $this->method;
            $form = "<form action=\"$action\" method=\"$method\">";
            $form .= "<table class=\"genform\" style=\"width: 100%;\">";
            /*$error = $this->validation_errors();
            if($error !== null && $error != "" && isset($error)){
                $form .= "<tr class=\"genformerrorrow\">";
                $form .= "<td class\"genformerrorcell\" colspan=\"2\">$error</td>";
                $form .= "</tr>";
            }*/
            foreach($this->fields as $field){
                unset($name);
                unset($description);
                $name = "";
                $description = "";
                if(isset($field->name)) $name = $field->name;
                if(isset($field->description)) $description = $field->description;
                $inbothrows = false;
                if(isset($field->inbothrows)) $inbothrows = $field->inbothrows;
                $form .= "<tr class=\"genformrow\" style=\"width: 100%;\">";
                
                $nameattr = "";
                if(isset($name) && $name != "" && $name !== null) $nameattr = " for=\"$name\"";
                
                if(!isset($inbothrows) || !$inbothrows){
                    $form .= "<td class=\"genformcell label\">";
                    $form .= "<label$nameattr>$description</label>";
                    $form .= "</td>";
                }
                $form .= "<td".((isset($inbothrows) && $inbothrows) ? " colspan=\"2\"" : "")." class=\"genformcell accessor\"  style=\"width: 100%;\">";
                $form .= $field->generateHTML();
                $form .= "</td>";
                
                $form .= "</tr>";
                if(($error = $this->validation_error($field)) !== false){
                    $form .= "<tr class=\"genformerrorrow\">";
                    $form .= "<td class\"genformerrorcell\" colspan=\"2\">$error</td>";
                    $form .= "</tr>";
                }
            }
            
            $form .= "</table>";
            $form .= "</form>";
            
            return $form;
        }
    }
}
if(!class_exists("Field")){
    class Field{
        public $description;
        public $tag;
        public $name;
        public $value;
        public $ivalue;
        public $type;
        public $inbothrows;
        public $class;
        public $childs = array();
        public $parameter = array();
        public $rule;
        private $parent = null;
        
        public function __construct(){
            empty($this->description);
            empty($this->tag);
            empty($this->name);
            empty($this->value);
            empty($this->type);
            empty($this->inbothrows);
            empty($this->class);
        }
        
        public function parent(){
            return $this->parent;
        }
        
        public function setRule($rule){
            $this->rule = $rule;
            return $this;
        }
        
        public function generateHTML(){
            unset($description);
            unset($tag);
            unset($name);
            unset($value);
            unset($ivalue);
            unset($type);
            unset($class);
            $description = "";
            $name = "";
            $ivalue = "";
            if(isset($this->description)) $description = $this->description;
            if(isset($this->tag)) $tag = $this->tag;
            if(isset($this->name)) $name = $this->name;
            $value = "";
            if(isset($this->value)) $value = $this->value;
            if(isset($this->ivalue)) $ivalue = $this->ivalue;
            $type = "";
            if(isset($this->type)) $type = ' type="'.$this->type.'"';
            if(isset($this->class)) $class = $this->class;
            
            $valueattr = "";
            if(isset($value) && $value != "" && $value !== null) $valueattr = " value=\"$value\"";
            $nameattr = "";
            $forattr = "";
            if(isset($name) && $name != "" && $name !== null) $nameattr = " name=\"$name\"";
            
        
            
            $html = "<".(isset($tag) ? $tag : "input")."$type".((isset($class)) ? " class=\"$class\"" : "")."$nameattr";
            foreach($this->parameter as $name => $tvalue){
                if($tvalue === null || empty($tvalue) || $tvalue == ''){
                    $html .= " $name";
                }
                else{
                    $html .= " $name=\"$tvalue\"";
                }
            }
            
            $html .= "$valueattr>";
            
            foreach($this->childs as $child){
                $html .= $child->generateHTML();
            }
            
            $html .= "$ivalue</".(isset($tag) ? $tag : "input").">";
            return $html;
        }
        
        public function addChild($c = null){
            if($c === null) $c = new Field();
            if($c instanceof Field){
                array_push($this->childs, $c);
            }
            $c->parent = $this;
            return $c;
        }
        
        public function setParameter($name, $value){
            $this->parameter[$this->attributize($name)] = $this->attributize($value);
            return $this;
        }
        
        public function attributize($string){
            return htmlspecialchars($string, ENT_QUOTES, "utf-8", false);
        }
        
        public function setAttribute($name, $value){
            $this->parameter[$this->attributize($name)] = $this->attributize($value);
            return $this;
        }
        
        public function getParameter($name){
            return $this->parameter[$this->attributize($name)];
        }
        
        public function getAttribute($name){
            return $this->parameter[$this->attributize($name)];
        }
        
        public function setDescription($description){
            $this->description = $description;
            return $this;
        }
        public function setTag($tag){
            $this->tag = $tag;
            return $this;
        }
        public function setName($name){
            $this->name = $name;
            return $this;
        }
        public function setValue($value){
            $this->value = $value;
            return $this;
        }
        public function setInnerValue($innervalue){
            $this->ivalue = $innervalue;
            return $this;
        }
        public function setType($type){
            $this->type = $type;
            return $this;
        }
        public function setFillBothRows($inbothrows){
            $this->inbothrows = $inbothrows;
            return $this;
        }
        public function setClass($class){
            $this->class = $class;
            return $this;
        }
    }
}

?>
