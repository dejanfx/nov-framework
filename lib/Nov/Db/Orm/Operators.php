<?php
namespace Nov\Db\Orm;
class Operators
{
    private $_type = null;
    private $_var1 = null;
    private $_var2 = null;
    
    private $_equalToo = null;
    
    const EQUAL = 0;
    const DISTINCT = 1;
    const MAYOR = 2;
    const MINOR = 3;
    
    function __construct($type, $var1, $var2, $equalToo=false)
    {
        $this->_type = $type;
        $this->_var1 = $var1;
        $this->_var2 = $var2;
        
        $this->_equalToo = $equalToo;
    }
    
    public function getType()
    {
        return $this->_type;
    }
    
    public function getVar1()
    {
        return $this->_var1;
    }
    
    public function getVar2()
    {
        return $this->_var2;
    }
    
    public function getEqualToo()
    {
        return $this->_equalToo;
    }
    
    static function equal($var1, $var2)
    {
        return new Operators(self::EQUAL, $var1, $var2);
    }
    
    static function distinct($var1, $var2)
    {
        return new Operators(self::DISTINCT, $var1, $var2);
    }
    
    static function mayor($var1, $var2, $equalToo=false)
    {
        return new Operators(self::MAYOR, $var1, $var2, $equalToo);
    }
    
    static function minor($var1, $var2, $equalToo=false)
    {
        return new Operators(self::MINOR, $var1, $var2, $equalToo);
    }
}