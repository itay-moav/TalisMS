<?php namespace Api;
/**
 * Discovery will list ALL APIs possible.
 * MAKE SURE TO DISABLE ON PUBLIC SITES, Or put under log in only users
 * 
 * @author Itay Moav
 * @Date  2020-06-25
 */
class TalisDiscoveryRead extends \Talis\Chain\aFilteredValidatedChainLink{
    
    /**
     */
    protected function get_next_bl():array{
        return [
            [ScrapAPIs::class,[]],            
            [\Talis\Chain\DoneSuccessfull::class,[]]
        ];
    }
}

/**
 * Go over the folders and list the items
 * @author itay
 *
 */
class ScrapAPIs extends \Talis\Chain\aChainLink
{
    public function process(): \Talis\Chain\aChainLink
    {
        $apis = $this->getDirContents('/home/itay/dev-repositories/director_moav/TalisMS/application/api');
        $filtered_apis = [];
        foreach($apis as $api){
            if(strpos($api,'.php')){
                $filtered_apis[] = str_replace('.php','',explode('api/',$api)[1]);
            }
        }
        
        $payload = new \stdClass;
        $payload->list = $filtered_apis;
        $this->Response->setPayload($payload);
        return $this;
    }
    
    /**
     * @param string $dir
     * @return array
     */
    private function getDirContents(string $dir):array{
        $results = [];
        $files =  array_diff(scandir($dir), ['..', '.']);
        
        foreach($files as $value){
            dbgn($value);
            if(!is_dir("{$dir}/{$value}")){
                $results[] = "{$dir}/{$value}";
            } elseif(is_dir("{$dir}/{$value}")) {
                $results[] = "{$dir}/{$value}";
                $results = array_merge($results,$this->getDirContents("{$dir}/{$value}"));
            }
        }
        return $results;
    }
}
