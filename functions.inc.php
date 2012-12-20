<?php

/**
 * 
 * @param array $data is an array that you want to send in POST
 * @param string $url the webservice url
 * @param string $token if the webserice need and token identification
 * @param string $method can be GET, POST, PUT, DELETE, INSERT
 * @param array $credentials with a username and password
 * @return array that's the return of the request
 */
function	request_curl($data, $url, $method)
{
        // init
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	
	// send data
	//envoi en post 
	if ($method == "POST")
	{       
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	}
	// envoi en get
	else if ($method == "GET")
	{		
		curl_setopt($ch, CURLOPT_HTTPGET, 1);
	}
	// envoi en delete
	else if ($method == "DELETE")
	{		
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
	}        
	// envoi en put 
	else if ($method == "PUT")
	{		
		/* Prepare the data for HTTP PUT. */	
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
	}
	
	// exec 
	$result = curl_exec($ch);
	
   if(curl_errno($ch))
   {
		// Le message d'erreur correspondant est affiché
		echo "ERREUR curl_exec : ".curl_error($ch);
   }

	$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	// fin de la requete 
	curl_close($ch);
			
	// on retourne le resultaS avec le code http du retour 
	$informations = array("http_status" => $http_status, "results" => $result);
	
	return $informations;
}

/**
 * 
 * @param string $tokenUrl the string to get a token 
 * @param string $_client_id your application client id 
 * @param string $_client_secret your application client secret
 * @param array $payload data send for the oAuth works
 * @return string a valid token 
 */
function request_token($tokenUrl, $_client_id, $_client_secret, $payload){
    $curl = curl_init($tokenUrl);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_USERPWD, "{$_client_id}:{$_client_secret}");
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
    
    $token = curl_exec($curl);
    $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    if ($responseCode != 200) { // An error occurred getting the token
            /*echo "ERROR<br/>";
            echo "An error for the request token was occured<br/>";            
            echo "<pre>";
            print_r($token);
            echo "</pre>";
            exit();*/
            return "invalid";
        
    }

    return $token;
}

/**
 * 
 * @param array $datas from a post request
 * @return array clean no more submit value inside the array
 */
function    formatData($datas){
    $keys = array_keys($datas);
    $values = array();
    for ($pos=0; $pos < count($keys); $pos++){
        if (!empty($datas[$keys[$pos]]))
            $values[$keys[$pos]] = $datas[$keys[$pos]];
    }
    
    return $values;
}

/**
 * 
 * @return string the current url
 */
function    curPageURL() {
    $pageURL = 'http';    
    if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
        $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
    }
    
    return $pageURL;
}

function    blowfishEncrypt($cleartext, $key){

        $cipher = mcrypt_module_open(MCRYPT_BLOWFISH, '', MCRYPT_MODE_CBC, '');
        $key = substr($key, 0, mcrypt_enc_get_key_size($cipher));
        $iv = '00000000';

        $cleartext_length = strlen($cleartext)%8;
        for($i=$cleartext_length; $i<8; $i++){
                $cleartext .= chr(8-$cleartext_length);
        }

        $cipherText='';
        // 128-bit blowfish encryption:
        if (mcrypt_generic_init($cipher, $key, $iv) != -1){
                // PHP pads with NULL bytes if $cleartext is not a multiple of the block size..
                $cipherText = mcrypt_generic($cipher,$cleartext );
                mcrypt_generic_deinit($cipher);
        }

        return base64_encode($cipherText);
}

function	formatReturnedDatas($dataSerialized)
{
	$tmp = unserialize($dataSerialized);
	$textDecode = json_decode($tmp['data']);
	return ($textDecode);
}

?>