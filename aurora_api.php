<?php

// content all functions we usually need
include_once("functions.inc.php"); 

// configuration file for use the client
include_once("aurora_api_config.php"); 

/**
 * 
 */
 class Aurora_Api 
 {
	 private $forumId;
	 
    // AURORA CONST 
    const TOKEN_URL     = 'http://auth.aurora-forums.fr/token.php';
    const SERVICE_URL   = 'http://services.aurora-forums.fr/';
    const REDIRECT_URL  = 'redirect_url';
    const SALT_CODE     = '$2a$07$QcVBDUngEDXlLAmTBzPk$';    
    
    // REST METHO
    const METHOD_POST   = "POST";
    const METHOD_GET    = "GET";
    const METHOD_PUT    = "PUT";
    const METHOD_DELETE = "DELETE";
    const METHOD_UPDATE = "UPDATE";
    
// GRANT TYPE CONST
    const GRANT_TYPE_AUTH_CODE          = 'authorization_code';
    const GRANT_TYPE_IMPLICIT           = 'token';
    const GRANT_TYPE_USER_CREDENTIALS   = 'password';
    const GRANT_TYPE_CLIENT_CREDENTIALS = 'client_credentials';
    const GRANT_TYPE_REFRESH_TOKEN      = 'refresh_token';
    const GRANT_TYPE_EXTENSIONS         = 'extensions';
    
    // TOKEN CONST
    const ACCESS_TOKEN  = 'access_token';
    const REFRESH_TOKEN = 'refresh_token';
     
    // SERVICES CONST
    const SERVICE_ID    = 'service_id';
     
    const SERVICE_CREATE_USER           = '101';
    const SERVICE_GET_USER_INFORMATION  = '102';
    const SERVICE_PASSWORD_USER         = '105';
    const SERVICE_AUTHENTICATE_USER     = '106';
	
    const SERVICE_CREATE_TOPIC          = '301';
    const SERVICE_GET_TOPIC             = '302';
    const SERVICE_GET_BOOKED_TOPIC      = '305';
    const SERVICE_SET_TOPIC_FAVORITE    = '306';    
    const SERVICE_UNSET_TOPIC_FAVORITE  = '307';    
    const SERVICE_COUNT_TOPIC           = '309';    
    const SERVICE_GET_CATEGORY          = '402';
	   
    const SERVICE_GET_FORUM             = '502';    
    const SERVICE_CATEGORIES_FORUM      = '506'; 
    const SERVICE_GET_CLIENT            = '513'; 
	
    const SERVICE_CREATE_POST           = '601';
    const SERVICE_GET_POST              = '602';
    const SERVICE_RATE_POST             = '606';
    const SERVICE_COUNT_POST            = '607';
	
	const SERVICE_PARAM_CATEGORY        = 'c';
    
    const LOGIN             = 'username';
    const PASSWORD          = 'password';
    const EMAIL             = 'email';
    const GENDER            = 'gender';
    const AVATAR            = 'avatar';
    const BIRTHDAY          = 'birthday';
    const USER_ID           = 'user_id';
    
    const TOPIC             = 'title';
    const POST_CONTENT      = 'post';
    const CATEGORY_ID       = 'fk_categories_id';
    const TOPIC_ID          = 'fk_topics_id';
    const POST_SUBJECT      = 'subject';
    const POST_TEXT         = 'text';
    const POST_ID           = 'post_id';
    const FORUM_ID          = 'forum_id';
    const FROM              = 'from';
    const TO                = 'nb_request';
    const FORUM_NAME        = 'name';
    const FORUM_DESCRIPTION = 'description';
    const FORUM_URL         = 'url';
    const FORUM_ICON        = 'icon_url';
    
    
        
     /**
      * constructeur
      */
     public function __construct() {
        session_start();
     }
     
	 public function setForumId($forumId)
	 {
		 $this->forumId = $forumId;
	 }
	 
     /**
      * This function set an access token and a refresh token by user credentials 
      * @param string $username the user login or email
      * @param string $password the user password
      * @return boolean if it's success true is return 
      */
     public function getAccessTokenFromPassword($username, $password) {        
        $tokenUrl = $this->getTokenUrl();
        $_client_id = $this->getClientID();
        $_client_secret = $this->getClientSecret();
        $payload = $this->getPayLoadPassword($username, $password);
        
        $result = request_token($tokenUrl, $_client_id, $_client_secret, $payload);        
                
        $token = json_decode($result, true);
        
        $access_token = $token["access_token"];        
        $this->setSessionInfos(self::ACCESS_TOKEN, $access_token);
        
        $refresh_token = $token["refresh_token"];        
        $this->setSessionInfos(self::REFRESH_TOKEN, $refresh_token);
        
        return $result;	
     }
     
     /**
      * This function set an access token and a refresh token by old refresh token
      * @return boolean if it's success true it's return 
      */
     public function getAccessTokenFromRefresh() {
        $tokenUrl = $this->getTokenUrl();
        $_client_id = $this->getClientID();
        $_client_secret = $this->getClientSecret();        
        $refreshToken = $this->getRefreshToken();
        
        $payload = $this->getPayLoadRefresh($refreshToken);
                       
        $result = request_token($tokenUrl, $_client_id, $_client_secret, $payload);        
        
        $token = json_decode($result, true);
        
        $access_token = $token["access_token"];        
        $this->setSessionInfos(self::ACCESS_TOKEN, $access_token);
        
        $refresh_token = $token["refresh_token"];        
        $this->setSessionInfos(self::REFRESH_TOKEN, $refresh_token);
        
     	return true;
     }
     
     /**
      * 
      * @return string the url of the services for tokens
      */
     public function getTokenUrl() {
         return self::TOKEN_URL;
     }
     
     /**
      * 
      * @return string the client id set in the configuration
      */
     public function getClientID() {
         return CLIENT_ID;
     }
     
     /**
      * 
      * @return string the clien secret create by configuration params
      */
     public function getClientSecret() {
         return blowfishEncrypt(CLIENT_ID . CLIENT_SECRET, self::SALT_CODE);
         //return crypt(CLIENT_ID . CLIENT_SECRET, self::SALT_CODE);
     }
     
     /**
      * 
      * @return string return the current url from where the request was called
      */
     public function getCurrentUrl() {
         return curPageURL();
     }
     
     /**
      * 
      * @param string $username the login or email of the user
      * @param string $password the password of the user
      * @param string $redirect_uri the uri where we want get the response
      * @return array for the request
      */
     public function getPayLoadPassword($username, $password, $redirect_uri = '') {
         if (empty($redirect_uri)) {
             // default
             $redirect_uri = $this->getCurrentUrl();
         }
         
        $grant_type = self::GRANT_TYPE_USER_CREDENTIALS;
     
        $payload = array(
            'redirect_uri' => $redirect_uri,
            'grant_type' => $grant_type,
            'username' => $username,
            'password' => $password
        );
        
        return $payload;
     }
     
     /**
      * 
      * @param string $refreshToken get from the oldest one save of the since the first access token
      * @param string $redirect_uri the uri where you want get the response
      * @return array for the request token
      */
     public function getPayLoadRefresh($refreshToken, $redirect_uri = '') {
         if (empty($redirect_uri)) {
             // default
             $redirect_uri = $this->getCurrentUrl();
         }
         
        $grant_type = self::GRANT_TYPE_REFRESH_TOKEN;
     
        $payload = array(
            'redirect_uri' => $redirect_uri,
            'grant_type' => $grant_type,
            'refresh_token' => $refreshToken
        );
        
        return $payload;
     }
     
     /**
      * 
      * @return string get the access token from the variable session
      */
     public function getAccessToken() {
         return $this->getSessionInfos(self::ACCESS_TOKEN);                 
     }
     
     /**
      * 
      * @return string get the refresh token from the variable session
      */
     public function getRefreshToken() {
         return $this->getSessionInfos(self::REFRESH_TOKEN);                 
     }    
     
     /**
      * 
      * @param string $key the key value inside the variable session
      * @return mix
      */
     public function getSessionInfos($key){
         if(!isset($_SESSION[$key]))
         {
             return "";
         }
         return $_SESSION[$key];
     }
     
     /**
      * 
      * @param string $key the key value we want inside the variable session
      * @param mix $value the value for the given key
      */
     public function setSessionInfos($key, $value){
         $_SESSION[$key] = $value;
     }
     
     public function deleteSessionInfos($key){
         unset($_SESSION[$key]);
     }
	 
	 public function destroySession(){
         unset($_SESSION);
		 session_destroy();
     }
     
     /**
      * This follow part contain all function about our service
      */
     
     // USER Services
     
     /**
      * 
      * @param string $email the user email for connection
      * @param type $password
      * @return type
      */
     public function connect($email, $password) {
         
         return $this->getAccessTokenFromPassword($email, $password);
     }
     
     /**
      * This function is called to get information about a user      
      * @param int $userId the user id who you want information
      * @return array with a field "operation": "1" for success & "0" for failure also "data" 
      */
     public function getUserInfos($userId = ''){
        $datas = array(
            self::ACCESS_TOKEN  =>  $this->getAccessToken(),
            self::REFRESH_TOKEN =>  $this->getRefreshToken(),
            self::SERVICE_ID    =>  self::SERVICE_GET_USER_INFORMATION,
            self::REDIRECT_URL  =>  $this->getCurrentUrl(),
            self::USER_ID       =>  $userId
        );
        
        $formatDatas = formatData($datas);
                                
        $response = request_curl($formatDatas, self::SERVICE_URL, self::METHOD_POST);
                                
        // ask a new token 
        $this->getAccessTokenFromRefresh();
                               
        $data = unserialize($response["results"]);
        $result = json_decode($data["data"], true);       
                
        return $result;   
     }
        
    // TOPIC Services
    public function createTopic($title, $post, $categoryId) {
        $datas = array(
            self::ACCESS_TOKEN      =>  $this->getAccessToken(),
            self::REFRESH_TOKEN     =>  $this->getRefreshToken(),
            self::SERVICE_ID        =>  self::SERVICE_CREATE_TOPIC,
            self::REDIRECT_URL      =>  $this->getCurrentUrl(),
            self::TOPIC             =>  $title,
            self::POST_CONTENT      =>  $post,
            self::CATEGORY_ID       =>  $categoryId
        );
        
        $formatDatas = formatData($datas);
        
        $response = request_curl($formatDatas, self::SERVICE_URL, self::METHOD_POST);
        
        // ask a new token 
        $this->getAccessTokenFromRefresh();
		
        return (formatReturnedDatas($response["results"])); 
    }
    
    public function getTopicInfos($topicId) {
        $datas = array(
            self::ACCESS_TOKEN      =>  $this->getAccessToken(),
            self::REFRESH_TOKEN     =>  $this->getRefreshToken(),
            self::SERVICE_ID        =>  self::SERVICE_GET_TOPIC,
            self::REDIRECT_URL      =>  $this->getCurrentUrl(),
            self::TOPIC             =>  $topicId
        );
        
        $formatDatas = formatData($datas);
        
        $response = request_curl($formatDatas, self::SERVICE_URL, self::METHOD_POST);
        
        // ask a new token 
        $this->getAccessTokenFromRefresh();
        
		return (formatReturnedDatas($response["results"])); 
    }
	
	public function getTopicsList($idCat, $nbTopics = 10, $startPosition = 0) {
        $datas = array(
            self::ACCESS_TOKEN      =>  $this->getAccessToken(),
            self::REFRESH_TOKEN     =>  $this->getRefreshToken(),
            self::SERVICE_ID        =>  self::SERVICE_GET_TOPIC,
            self::REDIRECT_URL      =>  $this->getCurrentUrl(),
			self::TOPIC				=>	$this->forumId . '&'  . $idCat . '&' . $nbTopics . '&' . $startPosition
        );
        
        $formatDatas = formatData($datas);
        
        $response = request_curl($formatDatas, self::SERVICE_URL, self::METHOD_POST);
        
        // ask a new token 
        $this->getAccessTokenFromRefresh();

		return (formatReturnedDatas($response["results"]));   
    }
    
    public function getTopicsBooked($nbTopics = 10, $startPosition = 0) {
            $datas = array(
            self::ACCESS_TOKEN      =>  $this->getAccessToken(),
            self::REFRESH_TOKEN     =>  $this->getRefreshToken(),
            self::SERVICE_ID        =>  self::SERVICE_GET_BOOKED_TOPIC,
            self::REDIRECT_URL      =>  $this->getCurrentUrl(),
			self::TOPIC				=>	$this->forumId . '&' . $nbTopics . '&' . $startPosition
        );
        
        $formatDatas = formatData($datas);
        
        $response = request_curl($formatDatas, self::SERVICE_URL, self::METHOD_POST);
        
        // ask a new token 
        $this->getAccessTokenFromRefresh();
        
        return (formatReturnedDatas($response["results"]));               
    }
    
    public function setTopicAsFavorite($topicId){
        $datas = array(
            self::ACCESS_TOKEN      =>  $this->getAccessToken(),
            self::REFRESH_TOKEN     =>  $this->getRefreshToken(),
            self::SERVICE_ID        =>  self::SERVICE_SET_TOPIC_FAVORITE,
            self::REDIRECT_URL      =>  $this->getCurrentUrl(),
            self::TOPIC_ID          =>  $topicId
        );
        
        $formatDatas = formatData($datas);
        
        $response = request_curl($formatDatas, self::SERVICE_URL, self::METHOD_POST);

        // ask a new token 
        $this->getAccessTokenFromRefresh();
                      
        return unserialize($response["results"]);              
    }
    
    public function unsetTopicAsFavorite($topicId) {
        $datas = array(
            self::ACCESS_TOKEN      =>  $this->getAccessToken(),
            self::REFRESH_TOKEN     =>  $this->getRefreshToken(),
            self::SERVICE_ID        =>  self::SERVICE_UNSET_TOPIC_FAVORITE,
            self::REDIRECT_URL      =>  $this->getCurrentUrl(),
            self::TOPIC_ID          =>  $topicId
        );
        
        $formatDatas = formatData($datas);
        
        $response = request_curl($formatDatas, self::SERVICE_URL, self::METHOD_POST);
        
        // ask a new token 
        $this->getAccessTokenFromRefresh();
        
        return unserialize($response["results"]);          
    }
    
    public function searchTopic(){
        $datas = array(
            self::ACCESS_TOKEN      =>  $this->getAccessToken(),
            self::REFRESH_TOKEN     =>  $this->getRefreshToken(),
            self::SERVICE_ID        =>  self::SERVICE_UNSET_TOPIC_FAVORITE,
            self::REDIRECT_URL      =>  $this->getCurrentUrl()
        );
        
        $formatDatas = formatData($datas);
        
        $response = request_curl($formatDatas, self::SERVICE_URL, self::METHOD_POST);
        
        // ask a new token 
        $this->getAccessTokenFromRefresh();
        
        return unserialize($response["results"]);            
    }
    
    public function getNbTopicByCategory($categoryId) {
        $datas = array(
            self::ACCESS_TOKEN      =>  $this->getAccessToken(),
            self::REFRESH_TOKEN     =>  $this->getRefreshToken(),
            self::SERVICE_ID        =>  self::SERVICE_COUNT_TOPIC,
            self::REDIRECT_URL      =>  $this->getCurrentUrl(),
            self::CATEGORY_ID       =>  $categoryId
        );
        
        $formatDatas = formatData($datas);
        
        $response = request_curl($formatDatas, self::SERVICE_URL, self::METHOD_POST);
        
        // ask a new token 
        $this->getAccessTokenFromRefresh();
        
		return (formatReturnedDatas($response["results"]));       
    }
    
    // CATEGORY Services 
    public function getCategoriesList() {
        $datas = array(
            self::ACCESS_TOKEN  =>  $this->getAccessToken(),
            self::REFRESH_TOKEN =>  $this->getRefreshToken(),
            self::SERVICE_ID    =>  self::SERVICE_GET_CATEGORY,
            self::REDIRECT_URL  =>  $this->getCurrentUrl(),
			self::FORUM_ID		=>	$this->forumId
        );
        
        $formatDatas = formatData($datas);
                    
        $response = request_curl($formatDatas, self::SERVICE_URL, self::METHOD_POST);
        
        // ask a new token 
        $this->getAccessTokenFromRefresh();
		
        return (formatReturnedDatas($response["results"]));                         
    }

    // FORUM Services
    
    public function getForumInfos($forumId) {
        $datas = array(
            self::ACCESS_TOKEN      =>  $this->getAccessToken(),
            self::REFRESH_TOKEN     =>  $this->getRefreshToken(),
            self::SERVICE_ID        =>  self::SERVICE_GET_FORUM,
            self::REDIRECT_URL      =>  $this->getCurrentUrl(),
            self::FORUM_ID          =>  $forumId                    
        );
        
        $formatDatas = formatData($datas);
        
        $response = request_curl($formatDatas, self::SERVICE_URL, self::METHOD_POST);
        
        // ask a new token 
        $this->getAccessTokenFromRefresh();      
        
        return (formatReturnedDatas($response["results"]));  
    }
    
    public function getClientForum() {
        $datas = array(
            self::ACCESS_TOKEN      =>  $this->getAccessToken(),
            self::REFRESH_TOKEN     =>  $this->getRefreshToken(),
            self::SERVICE_ID        =>  self::SERVICE_GET_CLIENT,
            self::REDIRECT_URL      =>  $this->getCurrentUrl(),
        );
        
        $formatDatas = formatData($datas);
               
        $response = request_curl($formatDatas, self::SERVICE_URL, self::METHOD_POST);
        
        // ask a new token 
        $this->getAccessTokenFromRefresh();      
        
        return unserialize($response["results"]);   
    }    
    

	
	public function getCategoryInfos($idCat) {
        $datas = array(
            self::ACCESS_TOKEN  =>  $this->getAccessToken(),
            self::REFRESH_TOKEN =>  $this->getRefreshToken(),
            self::SERVICE_ID    =>  self::SERVICE_GET_CATEGORY,
            self::REDIRECT_URL  =>  $this->getCurrentUrl(),
			self::SERVICE_PARAM_CATEGORY => self::SERVICE_PARAM_CATEGORY,
			self::CATEGORY_ID	=>	$idCat
        );
        
        $formatDatas = formatData($datas);
                    
        $response = request_curl($formatDatas, self::SERVICE_URL, self::METHOD_POST);
        
        // ask a new token 
        $this->getAccessTokenFromRefresh();
		
        return (formatReturnedDatas($response["results"]));                         
    }
    
    // POST Services
    public function createPost($topicId, $post) {
        $datas = array(
            self::ACCESS_TOKEN  =>  $this->getAccessToken(),
            self::REFRESH_TOKEN =>  $this->getRefreshToken(),
            self::SERVICE_ID    =>  self::SERVICE_CREATE_POST,
            self::REDIRECT_URL  =>  $this->getCurrentUrl(),
            self::TOPIC_ID      =>  $topicId,
            self::POST_TEXT     =>  $post
        );
        
        $formatDatas = formatData($datas);
                    
        $response = request_curl($formatDatas, self::SERVICE_URL, self::METHOD_POST);
        
        // ask a new token 
        $this->getAccessTokenFromRefresh();
        var_dump($response);
        return (formatReturnedDatas($response["results"]));     
    }
    
    public function getPostsList($topicId, $nbPosts = 10, $startPosition = 0) {
        $datas = array(
            self::ACCESS_TOKEN  =>  $this->getAccessToken(),
            self::REFRESH_TOKEN =>  $this->getRefreshToken(),
            self::SERVICE_ID    =>  self::SERVICE_GET_POST,
            self::REDIRECT_URL  =>  $this->getCurrentUrl(),
            self::TOPIC_ID      =>  $topicId . '&' . $nbPosts . '&' . $startPosition
        );
        
        $formatDatas = formatData($datas);
                    
        $response = request_curl($formatDatas, self::SERVICE_URL, self::METHOD_POST);
        
        // ask a new token 
        $this->getAccessTokenFromRefresh();

        return (formatReturnedDatas($response["results"]));              
    }
    
    public function getNbPostsByTopic($topicId) {
        $datas = array(
            self::ACCESS_TOKEN  =>  $this->getAccessToken(),
            self::REFRESH_TOKEN =>  $this->getRefreshToken(),
            self::SERVICE_ID    =>  self::SERVICE_COUNT_POST,
            self::REDIRECT_URL  =>  $this->getCurrentUrl(),
            self::TOPIC_ID      =>  $topicId
        );
        
        $formatDatas = formatData($datas);
                    
        $response = request_curl($formatDatas, self::SERVICE_URL, self::METHOD_POST);
        
        // ask a new token 
        $this->getAccessTokenFromRefresh();
        
        return (formatReturnedDatas($response["results"]));           
    }
 }

?>
