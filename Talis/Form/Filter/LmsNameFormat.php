<?php
/**
 * Formats a name according to what we
 * expects in SiTEL
 *
 * @author Itay
 */
class Form_Filter_LmsNameFormat implements Form_Filter_i{
    /**
     * (non-PHPdoc)
     * @see Form_Filter_i::filter()
     */
    public function filter($data){
        $tmp=explode('-',$data);
        foreach($tmp as &$name){
            $name=preg_replace('/Mc([A-Z])/','yyyy $1',trim($name));//can be done with one regexp, but I spared you :-D
            $name=preg_replace('/De([A-Z])/','xxxx $1',$name);
            $name=preg_replace('/Di([A-Z])/','zzzz $1',$name);
            $name=ucwords(strtolower($name));
            $name=str_replace("Yyyy ",'Mc',$name);
            $name=str_replace("Xxxx ",'De',$name);
            $name=str_replace("Zzzz ",'Di',$name);
        }
        return join('-',$tmp);
    }
}