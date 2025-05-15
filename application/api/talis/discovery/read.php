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
     * Recursively reads all PHP files in the API directory to build the API discovery list
     * Notice last element in the chain must implement  \Talis\commons\iRenderable
     * otherwise the response can not be rendered and it will error out after the last chainlink is processed
     */
    protected function get_next_bl():array{
        return [
            [ScrapAPIs::class,[]],            
            [\Talis\Chain\DoneSuccessfull::class,[]]
        ];
    }
}

/**
 * Recursively reads all PHP files in the API directory to build the API discovery list
 * @author itay
 *
 */
class ScrapAPIs extends \Talis\Chain\aChainLink
{
    public function process(): \Talis\Chain\aChainLink
    {
        $api_path = APP_PATH . '/api';
        $apis = $this->getDirContents($api_path);
        $filtered_apis = [];
        foreach($apis as $api){
            if(strpos($api,'.php')){
                dbgn($api);
                $filtered_apis[] = str_replace('.php','',explode($api_path,$api)[1]);
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
            //dbgn($value);
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
