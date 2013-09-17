baolizer-cron
=============

This is the core engine of a bigger structure, wich loads data from your Podio app (Leads is preconfigured)
and provides as many data output repositories as you define.

The preconfigured structure is:

/
/cron
 (cron.your-server.tld)
 json.php
 webview.php

/public (data output repository)
 (public.your-server.tld)
 webview/*
 items.json

/staff (data output repository)
 (staff.your-server.tld)
 webview/*
 items.json

Usage:

- run /cron/json.php

Connects to Podio API (/cron/inc/podio.config.inc.php)
Fetches all data and writes as defined in /cron/inc/podio.json.inc.php the 'items.json' files to each data repository

- run /cron/webview/webview.php (needs development to work properly)
Takes the items.json file in each repository and generates a static html file by a template /cron/res/tpl/item.default
All resources like .jpg or .css are written directly to the .html file

- use the data
You can use each data repository to create your own architecture on it - like display the items on google maps
or use the data for a mobile app :)
