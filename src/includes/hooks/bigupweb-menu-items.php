<?php
/**
 * BigupWeb Custom Menu Items
 * 
 * These hooks customise the built-in nav bar to show items we want.
 *
 * @package    WHMCS
 * @author     Jefferson Real <me@jeffersonreal.uk>
 * @copyright  Copyright (c) Jefferson Real 2024
 * @license    GPL3
 * @version    0.1
 */


if (!defined('WHMCS'))
    die('You cannot access this file directly.');

use WHMCS\View\Menu\Item as MenuItem;



add_hook( 'ClientAreaPrimaryNavbar', 1, function( MenuItem $primaryNavbar ) {
    
    // Remove 'Home'.
    if ( !is_null( $primaryNavbar->getChild( 'Home' ) ) ) {
        $primaryNavbar->removeChild( 'Home' );
    }
    
    // Remove 'Announcements'.
    if ( !is_null( $primaryNavbar->getChild( 'Announcements' ) ) ) {
        $primaryNavbar->removeChild( 'Announcements' );
    }
    
    // Remove 'Network Status'.
    if ( !is_null( $primaryNavbar->getChild( 'Network Status' ) ) ) {
        $primaryNavbar->removeChild( 'Network Status' );
    }
    
    // Remove 'Store'.
    if ( !is_null( $primaryNavbar->getChild( 'Store' ) ) ) {
        $primaryNavbar->removeChild( 'Store' );
    }
    
    // Rename 'Domains' to 'My Domains' (only shown when client logged in).
    if ( !is_null( $primaryNavbar->getChild( 'Domains' ) ) ) {
        $primaryNavbar->getChild( 'Domains' )->setLabel( 'My Domains' )->setOrder(4);
    }

} );

