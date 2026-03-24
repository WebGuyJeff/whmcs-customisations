<?php

/**
 * Fetch grouped product data for use in navs etc. 
 * 
 */

if (!defined('WHMCS'))
    die('You cannot access this file directly.');

use WHMCS\Database\Capsule;
use WHMCS\Session;


add_hook('ClientAreaPage', 1, function ($vars) {
    
    $groups = [];
    
    /* ---------------------------------------
       Get active WHMCS currency
    --------------------------------------- */
    
    $currencyId = Session::get('currency');
    
    if (!$currencyId) {
        $currencyId = Capsule::table('tblcurrencies')
            ->where('default', 1)
            ->value('id');
    }
    
    $currency = Capsule::table('tblcurrencies')
        ->where('id', $currencyId)
        ->first(['id','code','prefix','suffix']);
    
    $currencyCode = $currency->code;
    
    /* ---------------------------------------
       Call product data from API
    --------------------------------------- */

    $adminUsername = 'apiuser';

    $results = localAPI('GetProducts', [ 'currencyid' => $currencyId ], $adminUsername);

    if ($results['result'] !== 'success') {
        return ['navProductGroups' => []];
    }

    /* ---------------------------------------
       Load database metadata
    --------------------------------------- */

    // Products.
    $productRows = Capsule::table('tblproducts')
        ->get(['id', 'hidden', 'short_description', 'is_featured']);

    $hiddenProducts = [];
    $productShortDescription = [];
    $featuredProducts = [];

    foreach ($productRows as $row) {

        if ($row->hidden) {
            $hiddenProducts[$row->id] = 1;
        }

        $productShortDescription[$row->id] = $row->short_description;

        if ($row->is_featured) {
            $featuredProducts[$row->id] = 1;
        }
    }

    // Product groups.
    $groupRows = Capsule::table('tblproductgroups')
        ->get(['id', 'name', 'icon', 'hidden']);

    $hiddenGroups = [];
    $groupNames = [];

    foreach ($groupRows as $row) {

        if ($row->hidden) {
            $hiddenGroups[$row->id] = 1;
        }

        $groupNames[$row->id] = $row->name;
        $groupIcons[$row->id] = $row->icon;
    }

    // Product group slugs.
    $groupSlugs = Capsule::table('tblproducts_slugs')
        ->pluck('group_slug', 'group_id')
        ->toArray();

    /* ---------------------------------------
       Process API products
    --------------------------------------- */

    if (!empty($results['products']['product'])) {

        $products = $results['products']['product'];

        // Handle WHMCS single-product response edge case
        if (isset($products['pid'])) {
            $products = [$products];
        }

        foreach ($products as $product) {

            // skip hidden products or hidden groups
            if (!empty($hiddenProducts[$product['pid']]) || !empty($hiddenGroups[$product['gid']])) {
                continue;
            }

            $gid       = $product['gid'];
            $groupSlug = $groupSlugs[$gid] ?? "?gid=" . $gid;

            if (!isset($groups[$gid])) {
                $groups[$gid] = [
                    'gid'      => $gid,
                    'name'     => $groupNames[$product['gid']],
                    'icon'     => $groupIcons[$product['gid']],
                    'link'     => "/store/" . $groupSlug,
                    'products' => [],
                    'featured' => null
                ];
            }

            /* -------------------------------
               Extract GBP pricing
            ------------------------------- */

            $price  = null;
            $period = null;
            $prefix = null;

            if (!empty($product['pricing'][$currencyCode])) {

                $periods = $product['pricing'][$currencyCode];
                $periods = array_filter($periods, fn($value) => is_numeric($value) && (float)$value > 0);

                if (!empty($periods)) {
                    $cheapest = min( array_map('floatval', $periods) );
                    $period   = array_search($cheapest, array_map('floatval', $periods));
                    $price    = number_format($cheapest, 2);
                } else {
                    $price = '0';
                }
                
                $prefix = $product['pricing'][$currencyCode]['prefix'];
            }

            /* -------------------------------
               Build product item
            ------------------------------- */

            $item = [
                'pid'         => $product['pid'],
                'name'        => $product['name'],
                'description' => strip_tags($productShortDescription[$product['pid']] ?? ''),
                'link'        => stripslashes($product['product_url']),
                'price'       => $price,
                'period'      => $period,
                'prefix'      => $prefix
            ];

            $groups[$gid]['products'][] = $item;

            /* -------------------------------
               Assign featured product
            ------------------------------- */

            if (empty($groups[$gid]['featured']) && !empty($featuredProducts[$item['pid']])) {
                $groups[$gid]['featured'] = $item;
            }
        }
    }

    // DEBUG.
    /*
    echo '<pre style="position:fixed;bottom:0;left:0;z-index:9999;background:#fff;color:#000;height:20em;width:100%;overflow:scroll;">'
        . print_r($groups, true)
        . '</pre>';
    */

    return ['navProductGroups' => array_values($groups)];
});
