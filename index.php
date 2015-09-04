<?php
/*  TWITTER LOGIN BASIC - Tutorial
 *  file            - index.php
 *  Developer       - Krishna Teja G S
 *  Website         - http://packetcode.com/apps/twitter-login/
 *  Date            - 4th Sept 2015
 *  license         - GNU General Public License version 2 or later
 */

/* 
 * CODE BREAKDOWN
 *   PART 1 - DEFINING (loads files,global constants,session enabling)
 *   PART 2 - PROCESS ( check for logout,user session,call back request ) 
 *   PART 3 - FRONT END (display login url or user data)
 *
 */


/* 
 * PART 1 - DEFINING 
 */
// Load the library files
require_once('twitteroauth/OAuth.php');
require_once('twitteroauth/twitteroauth.php');
// define the consumer key and secet and callback
define('CONSUMER_KEY', 'YOUR_CONSUMER_KEY');
define('CONSUMER_SECRET', 'YOUR_CONSUMER_SECRET');
define('OAUTH_CALLBACK', 'YOUR_CONSUMER_CALLBACK_URL');
// start the session
session_start();

/* 
 * PART 2 - PROCESS
 * 1. check for logout
 * 2. check for user session  
 * 3. check for callback
 */

// 1. to handle logout request
if(isset($_GET['logout'])){
	//unset the session
	session_unset();
	// redirect to same page to remove url paramters
	$redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
  	header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
}


// 2. if user session not enabled get the login url
if(!isset($_SESSION['data']) && !isset($_GET['oauth_token'])) {
	// create a new twitter connection object
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
	// get the token from connection object
	$request_token = $connection->getRequestToken(OAUTH_CALLBACK); 
	// if request_token exists then get the token and secret and store in the session
	if($request_token){
		$token = $request_token['oauth_token'];
		$_SESSION['request_token'] = $token ;
		$_SESSION['request_token_secret'] = $request_token['oauth_token_secret'];
		// get the login url from getauthorizeurl method
		$login_url = $connection->getAuthorizeURL($token);
	}
}

// 3. if its a callback url
if(isset($_GET['oauth_token'])){
	// create a new twitter connection object with request token
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['request_token'], $_SESSION['request_token_secret']);
	// get the access token from getAccesToken method
	$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);
	if($access_token){	
		// create another connection object with access token
		$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
		// set the parameters array with attributes include_entities false
		$params =array('include_entities'=>'false');
		// get the data
		$data = $connection->get('account/verify_credentials',$params);
		if($data){
			// store the data in the session
			$_SESSION['data']=$data;
			// redirect to same page to remove url parameters
			$redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
  			header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
		}
	}
}

/* 
 * PART 3 - FRONT END 
 *  - if userdata available then print data
 *  - else display the login url
*/

if(isset($login_url) && !isset($_SESSION['data'])){
	// echo the login url
	echo "<a href='$login_url'><button>Login with twitter </button></a>";
}
else{
	// get the data stored from the session
	$data = $_SESSION['data'];
	// echo the name username and photo
	echo "Name : ".$data->name."<br>";
	echo "Username : ".$data->screen_name."<br>";
	echo "Photo : <img src='".$data->profile_image_url."'/><br><br>";
	// echo the logout button
	echo "<a href='?logout=true'><button>Logout</button></a>";
} 
