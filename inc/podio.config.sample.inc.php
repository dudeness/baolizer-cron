<?php

/*
 * Podio API access data.
 * You find them in your podio ui.
 */
define('PODIO_USER_ID', '');
define('PODIO_USER_SECRET', '');
define('PODIO_APP_ID', '');
define('PODIO_APP_TOKEN', '');

$podioConfig = array();

/*
 * Podio API supports 500 item results per filter request, thats why
 * the requests needed to be split up anyway.
 * In used cases it might be the best to use the max value of 500
 */
$podioConfig['filter_split'] = 500;
/*
 * Podio API is limited to a number of requests per hour and day.
 * In current sate of development cache does not expire!
 * 
 * 0 - absolutly no caching
 * 1 - caches field config data
 * 2 - caches even field values - no podio requests are made - development only!
 */
$podioConfig['filter_cache'] = 0;

/*
 * The podio leads app does not support latlng natively, but if you whish
 * to display results on a map this application supports a full
 * latlng integration.
 * 
 * @enabled
 *     if set to true, you use that function
 * 
 * @view
 *     define the field in your podio's app which stores the data
 *     needed to configurated as plain text in podio.
 *     
 * @query_fields
 *     an numeric array of existing fields of your podio app which are
 *     used if no latlng value is already stored to query openstreetmap
 *     to get the data and write them to your podio app
 */
$podioConfig['latlng'] = array(
    'enabled' => false,
    'view' => array(
        'field' => 'latlon',
        'separator' => 'x',
    ),
    'query_fields' => array(
        'street-address',
        'zip-codepost-code',
        'city',
        'state-provins-or-territory',
    ),
);
