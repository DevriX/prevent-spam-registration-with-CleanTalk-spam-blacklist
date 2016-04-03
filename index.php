<?php

/*
Plugin Name: Prevent spam registration with CleanTalk's spam blacklist
Plugin URI: 
Description: Preventing spam registration on your blog with CleanTalk's own spam blacklist tool
Author: Samuel Elh
Version: 0.1
Author URI: http://samelh.com
*/

// Ignore direct access
defined( 'ABSPATH' ) || exit;

/**
  * using cleantalk's spam blacklist to prevent spam registration on your WordPress site 
  * If you go https://cleantalk.org/blacklists?record=[any_email] you will get to see
  * the spamming record of that email address, whether that email is spam or not, and
  * we will be getting the content of that dynamic page everytime someone attempts to
  * register on our WordPress blog, if found spam then push them away..
  * @param $email_address (str) email address to verify
  */

if( ! function_exists('is_spam_check') ) {
	
	function is_spam_check( $email_address ) {
		
		if( ! ( $email_address > '' ) ) // WordPress will be dealing with this one already (validation)
			return;

		if( ! is_numeric( strpos( $email_address, "@" ) ) ) // WordPress will be dealing with this one already (validation)
			return;		

		$html = file_get_contents("https://cleantalk.org/blacklists?record=$email_address");
		$is_spam = is_numeric( strpos( $html, "is reported as spam" ) );
		
		// boolean (true|false)
		return (bool) $is_spam;
	
	}

}

/**
  * Verify the email address of the user before the registration is complete
  * using is_spam_check function to check for spam
  */

add_filter( 'registration_errors', function( $errors, $user_login, $user_email ) {

    if ( is_spam_check( $user_email ) ) {
        $errors->add( 'spam_registration', "Sorry, we can't complete your request." );
        // You can do additional stuff here to punish the spammer, like recording this email and timestamp and other info, or maybe telling the spammer to FO (P.S, bots!)
    }

    return $errors;

}, 10, 3 );

// That's it. A little note, if ever CleanTalk.org does not want us to do this process of hitting their website for records check, I am completely deleting this little plugin as soon as I get a message from them.
