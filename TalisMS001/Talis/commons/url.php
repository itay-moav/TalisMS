<?php namespace commons\url;

const DONT_FORCE_SCHEMA = 0,
      FORCE_HTTP        = 1,
      FORCE_HTTPS       = 2
;

/**
 * checks on $_SERVER if current schema is https or not
 * @return boolean
 */
function is_https(){
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on');
}

/**
 * return the schema for the url according to input flags
 * @param integer $rule DONT_FORCE_SCHEMA = 0, FORCE_HTTP = 1, FORCE_HTTPS = 2
 * @return string https | http
 */
function schema($rule=DONT_FORCE_SCHEMA){
    switch($rule){
        case DONT_FORCE_SCHEMA:
            return is_https()?'https':'http';
            
        case FORCE_HTTPS:
            return 'https';
            
        case FORCE_HTTP:
            return 'http';
            
        default:
            throw new \Exception_MissingParam('$rule has to have one of DONT_FORCE_SCHEMA|FORCE_HTTPS|FORCE_HTTP');
    }
}

/**
 * @param integer  $schema DONT_FORCE_SCHEMA = 0, FORCE_HTTP = 1, FORCE_HTTPS = 2
 * @param string $subdomain www,next,beta,aeonflux,api,ramasaml
 * @return string http[s]://[subdomain].base_url
 */
function url($schema=DONT_FORCE_SCHEMA,$subdomain=SUBDOMAIN):string{
    return schema($schema) . '://' . \app_env()['u']($subdomain);
}

/**
 * Alyasing URL()
 * 
 * @param string $schema
 * @param string $subdomain
 * @return string
 */
function home($schema=DONT_FORCE_SCHEMA,$subdomain=SUBDOMAIN):string{
    return url($schema,$subdomain);
}

/**
 * return the current org's landing page url
 * 
 * @param string $schema
 * @param string $subdomain
 * @return string
 */
function home_org($schema=DONT_FORCE_SCHEMA,$subdomain=SUBDOMAIN):string{
    return home($schema,$subdomain) . get_org_path();
}

/**
 * TODO decide how to handle input which does not result in an org path
 * 
 * get org path in the url 
 * 
 * @param mixed $organization_identifier
 * @return string
 */
function get_org_path($organization_identifier=null):string{
    static $org_data = [
        3   => 'mwhc',
        4   => 'mguh',
        7   => 'mharbor',
        8   => 'mfsmc',
        9   => 'mgsh',
        11  => 'mumh',
        16  => 'mh',
        28  => 'msmh',
        36  => 'msmhc'
    ];
    
    if(!$organization_identifier){
        $path = \Organization_Current::path();
        if($path){
            $path = "/{$path}";
        }
        return $path;
    }
    
    if(\is_numeric($organization_identifier)){
        $org_data[$organization_identifier] = $org_data[$organization_identifier]??(new \Redis_Organization_Main($organization_identifier))->org()->get()->path;
        return "/{$org_data[$organization_identifier]}";
    }
    return "/{$organization_identifier}";//assuuming it is path
}

/**
 * Return the url for the static assets [files] for current subdomain
 * 
 * @param string $schema
 * @return string
 */
function files($schema=DONT_FORCE_SCHEMA){
    return url($schema,'www') . '/files';
}

/**
 * Return the url for the javascript files folder (defaults to current subdomain and schema, no org
 *
 * @param string $schema
 * @return string
 */
function js($schema=DONT_FORCE_SCHEMA,$subdomain=SUBDOMAIN):string{
    return url($schema,$subdomain) . '/js';
}

/**
 * Return the url for the css files folder (defaults to current subdomain and schema, no org
 *
 * @param string $schema
 * @param string $subdomain defaults to current
 * @return string
 */
function css($schema=DONT_FORCE_SCHEMA,$subdomain=SUBDOMAIN):string{
    return url($schema,$subdomain) . '/css';
}

/**
 * Return the url for the image files folder (defaults to current subdomain and schema, no org
 *
 * @param string $schema
 * @param string $subdomain defaults to current
 * @return string
 */
function img($schema=DONT_FORCE_SCHEMA,$subdomain=SUBDOMAIN):string{
    return url($schema,$subdomain) . '/img';
}

/**
 *  
 *  @param unknown $schema
 */
function www($schema=DONT_FORCE_SCHEMA){
	return url($schema,'www');
}

function next($schema=DONT_FORCE_SCHEMA){
    return url($schema,'next');
}

function aeon($schema=DONT_FORCE_SCHEMA){
    return url($schema,'aeonflux');
}

/**
 * get full org url for www
 * 
 * @param integer $schema DONT_FORCE_SCHEMA = 0, FORCE_HTTP = 1, FORCE_HTTPS = 2
 * @param mixed $organization_identifier org_id | org_path
 * @return string
 */
function www_org($schema = DONT_FORCE_SCHEMA, $organization_identifier = null){
    return www($schema) . get_org_path($organization_identifier);
}

/**
 * get full org url for next
 *
 * @param integer $schema DONT_FORCE_SCHEMA = 0, FORCE_HTTP = 1, FORCE_HTTPS = 2
 * @param mixed $organization_identifier org_id | org_path
 * @return string
 */
function next_org($schema = DONT_FORCE_SCHEMA, $organization_identifier = null){
    return next($schema) . get_org_path($organization_identifier);
}
