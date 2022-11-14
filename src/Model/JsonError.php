<?php 

namespace App\Model;

class JsonError
{
    /**
    *
    * @var string
    */
    public $message;
    
    /**
    *
    * @var integer
    */
    public $code_error;
    
    /**
    */
    public function construct($message, $code)
    {
        $this->message = $message;
        $this->code_error = $code;
    }
}