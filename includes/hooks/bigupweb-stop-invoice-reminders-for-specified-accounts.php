<?php

/**
 * Stop "Invoice Payment Reminder" emails for specific clients.
 * @see https://whmcs.community/topic/281489-disable-payment-reminder-for-select-customers/
 */


/**

# Commented out as I think this is breaking invoice sending for the account.


use WHMCS\Database\Capsule;

add_hook("EmailPreSend", 1, function( $vars ){

    $account_ids_to_block = array( 4 );
	
    if ( $vars['messagename'] === "Invoice Payment Reminder" ){
    	$getClientId = Capsule::table( "tblinvoices" )->where( "id", $vars['relid'] )->first();
    	// Add client 
      	if ( in_array( $getClientId->userid, $account_ids_to_block ) ) {
        	return array( "abortsend" => true );
        }
    }
    elseif ( $vars['messagename'] === "First Invoice Overdue Notice" ){
    	$getClientId = Capsule::table( "tblinvoices" )->where( "id", $vars['relid'] )->first();
      	if ( in_array($getClientId->userid, $account_ids_to_block ) ){
        	return array( "abortsend" => true );
        }
    }
    elseif ( $vars['messagename'] === "Second Invoice Overdue Notice" ){
    	$getClientId = Capsule::table( "tblinvoices" )->where( "id", $vars['relid'] )->first();
      	if ( in_array($getClientId->userid, $account_ids_to_block ) ){
        	return array( "abortsend" => true );
        }
    }
    elseif ( $vars['messagename'] === "Third Invoice Overdue Notice" ){
    	$getClientId = Capsule::table( "tblinvoices" )->where( "id", $vars['relid'] )->first();
      	if ( in_array($getClientId->userid, $account_ids_to_block ) ){
        	return array( "abortsend" => true );
        }
    }
} );

*/
