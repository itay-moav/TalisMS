<?php
/**
 * This class encrypts using 
 * AES 256 algoritem
 * CBC packing mode
 * 
 * Copied from https://github.com/simplesamlphp/simplesamlphp/issues/228 
 * 
 * @author itaymoav
 */
class Encryption_AES256CBC{
    const ZERO_PADDING = true; 
				
	private 	$method = 'AES-256-CBC',
				/**
				 * @var string an encoded key, this is the shared private secret we use to decode/encode
				 */
				$key    = '',
				/**
				 * @var string
				 */
				$iv     = '',
				/**
				 * @var int
				 */
				$iv_size = 0,
				/**
				 * @var int Size of the block of the clear data to be encrypted 
				 */
				$block_size = 32
	;
	
	/**
	 * 
	 * @param string $key base64 decoded please.
	 * @param string $iv (optional, depends if I am the decrypting or encrypting guy)
	 */
	public function __construct($key,$iv=''){
		$this->key  = $key;
		$this->iv_size = openssl_cipher_iv_length($this->method);
		$this->iv = $iv?base64_decode($iv):$this->generate_iv();
		
	}
	
	/**
	 * Openssl generated IV
	 * @return string
	 */
	protected function generate_iv(){
		return $iv = openssl_random_pseudo_bytes($this->iv_size);
	}
	
	/**
	 * getter for iv
	 * @return string base 64 encoded iv string
	 */
	public function iv(){
		return base64_encode($this->iv);
	}
	
	/**
	 * Debugs the openssl stuff
	 * @param string $data usually the un encrypted string I wish to encrypt
	 */
	public function debug_properties($data){
	    echo "AVBAILABLE CYPHERS:\n";
	    print_r(openssl_get_cipher_methods());
	    echo "\n----------------------\n";
	    echo "openssl version text: " . OPENSSL_VERSION_TEXT . "\n";
	    echo "openssl version number:  " . OPENSSL_VERSION_NUMBER . "\n";
	    echo "\ndata {$data}\n";
	    echo "\nmethod {$this->method}\n";
	    echo "\nkey {$this->key}\n";
	    echo "\npadding " . OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING . "\n";
	    echo "\niv {$this->iv}\n";
	     
	}
	
	/**
	 * Encrypts
	 * 
	 * @param string $clear un encrypted
	 * @return string encrypted data
	 */
	public function encrypt($clear,$zero_padding=false){
	    //$this->debug_properties($clear);
	    
	    //pad data
	    $pad = $this->block_size - (strlen($clear) % $this->block_size);
	    //PKCS#7 is using the ascii code of the character as the amount of padding 
	    $padding_char = $zero_padding? chr(0) : chr($pad);
	    $clear .= str_repeat($padding_char, $pad);
	    
        $encrypted_data = openssl_encrypt ($clear, $this->method, $this->key,OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, $this->iv);
        $err = openssl_error_string();
        if(!$encrypted_data){
            fatal($err);
            throw new Exception("Failed ENcryption with [{$err}]");
        }
		return base64_encode($encrypted_data);		
	}
	
	/**
	 * decrypts
	 * 
	 * @param string $encrypted_data
	 * @return string
	 */
	public function decrypt($encrypted_data){
	    $decoded_data = base64_decode($encrypted_data);
        $clear = openssl_decrypt($decoded_data, $this->method, $this->key, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, $this->iv);
        $err = openssl_error_string();
	    if(!$clear){
            fatal($err);
            throw new Exception("Failed DEcryption with [{$err}]");
        }
        //check if the CLEAR string has the padding and remove them.
        $last_char_code = ord(substr($clear, -1));
        if($last_char_code == ord(substr($clear, -1 * $last_char_code,1))){
            $clear = substr($clear,0,strlen($clear) - $last_char_code);
        }
        return $clear;
    }
}