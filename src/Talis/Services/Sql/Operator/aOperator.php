<?php namespace Talis\Services\Sql\Operator;

abstract class aOperator{
    
    /**
     * Raw value(s) before operator is applied to them
     * 
     * @var array
     */
    protected $raw_values = [];
    
    /**
     * Operator text with place holders, for example "MIN(:v1)"  or "BETWEEN :v1 AND :v2"
     * @var string
     */
    protected $operator_text = '';
    
    public function __construct(...$raw_values){
        $this->raw_values = $raw_values;
    }
    
    /**
     * returns the operator with place holders and array of values to be cleaned.
     * 
     * @return array
     */
    public function cleaned_operator(string $prefix = ''):array{
        $ret = [
            'str'    => str_replace(':',':' . $prefix, $this->operator_text),
            'params' => []
        ];
        
        foreach($this->raw_values as $i=>$value){
            $ret['params'][":{$prefix}v{$i}"] = $value;
        }
        
        return $ret;
    }
    
    /**
     * Creates the operator text with the raw values.
     * 
     * @return string
     */
    public function raw_operator():string{
        $ret = $this->operator_text;
        foreach($this->raw_values as $i=>$value){
            $ret = str_replace(":v{$i}","'{$value}'",$ret);
        }
        return $ret;
    }
    
    /**
     * alias for raw_operator
     */
    public function __toString(){
        return $this->raw_operator();
    }
}
