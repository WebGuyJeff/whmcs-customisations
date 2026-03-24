<?php

/**
 * Fetch TLD pricing and category data for use in navs etc. 
 * 
 */

if (!defined('WHMCS'))
    die('You cannot access this file directly.');

use WHMCS\Database\Capsule;
use WHMCS\Session;


add_hook('ClientAreaPage', 1, function ($vars) {

    /* ---------------------------------------
       Get visitor currency
    --------------------------------------- */

    $currencyId = Session::get('currency');

    if (!$currencyId) {
        $currencyId = Capsule::table('tblcurrencies')
            ->where('default', 1)
            ->value('id');
    }

    $currencyPrefix = Capsule::table('tblcurrencies')
        ->where('id', $currencyId)
        ->value('prefix');

    /* ---------------------------------------
       Get spotlight TLD configuration
    --------------------------------------- */

    $spotlightConfig = Capsule::table('tblconfiguration')
        ->where('setting', 'SpotlightTLDs')
        ->value('value');

    $spotlightTlds = [];

    if ($spotlightConfig) {
        $spotlightTlds = array_filter(
            array_map(
                fn($tld) => ltrim(trim($tld), '.'),
                explode(',', $spotlightConfig)
            )
        );
    }

    /* ---------------------------------------
       Fetch enabled TLDs + pricing
    --------------------------------------- */

    $rows = Capsule::table('tbldomainpricing as dp')
        ->join('tblpricing as p', function ($join) use ($currencyId) {

            $join->on('p.relid', '=', 'dp.id')
                 ->where('p.type', 'domainregister')
                 ->where('p.currency', $currencyId);

        })
        ->select(
            'dp.extension',
            'dp.order',
            'p.msetupfee as register_price'
        )
        ->where('dp.extension', '!=', '')
        ->orderBy('dp.order')
        ->get();

    /* ---------------------------------------
       Build output arrays
    --------------------------------------- */

    $all       = [];
    $spotlight = [];

    foreach ($rows as $row) {

        $extension    = $row->extension;
        $price        = number_format((float)$row->register_price, 2);
        $logoFilename = str_replace('.','',$extension);
        $logoFilepath = "/assets/img/tld_logos/{$logoFilename}.png";
        
        $logo = '';
        
        if ( file_exists( __DIR__ . '/../..' . $logoFilepath ) ) {
            $logo = $logoFilepath;
        }

        $item = [
            'tld'    => $extension,
            'price'  => $price,
            'prefix' => $currencyPrefix,
            'logo'   => $logo
        ];

        $all[] = $item;

        if (in_array( substr($extension, 1 ), $spotlightTlds)) {
            $spotlight[] = $item;
        }
    }

    /* ---------------------------------------
       Return structure
    --------------------------------------- */

    $tldPricing = [
        'all' => $all,
        'spotlight' => $spotlight
    ];

    // DEBUG.
    /*
    echo '<pre style="position:fixed;bottom:0;left:0;z-index:9999;background:#fff;color:#000;height:20em;width:100%;overflow:scroll;">'
        . print_r($tldPricing, true)
        . '</pre>';
    */

    return [ 'tldPricing' => $tldPricing ];
});    


