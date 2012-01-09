<?php

/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005-2010 The University of Queensland,                |
// | Australian Partnership for Sustainable Repositories,                 |
// | eScholarship Project                                                 |
// |                                                                      |
// | Some of the Fez code was derived from Eventum (Copyright 2003, 2004  |
// | MySQL AB - http://dev.mysql.com/downloads/other/eventum/ - GPL)      |
// |                                                                      |
// | This program is free software; you can redistribute it and/or modify |
// | it under the terms of the GNU General Public License as published by |
// | the Free Software Foundation; either version 2 of the License, or    |
// | (at your option) any later version.                                  |
// |                                                                      |
// | This program is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
// | GNU General Public License for more details.                         |
// |                                                                      |
// | You should have received a copy of the GNU General Public License    |
// | along with this program; if not, write to:                           |
// |                                                                      |
// | Free Software Foundation, Inc.                                       |
// | 59 Temple Place - Suite 330                                          |
// | Boston, MA 02111-1307, USA.                                          |
// +----------------------------------------------------------------------+
// | Authors: Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+

include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'config.inc.php';
include_once(APP_INC_PATH . 'class.favourites.php');
include_once(APP_INC_PATH . 'class.lister.php');
include_once(APP_INC_PATH . 'class.user.php');

set_time_limit(0);
error_reporting(~E_WARNING && ~E_NOTICE);
echo "<pre>";
main();
echo "</pre>";
function main()
{
	echo "====================================\n";
	echo "Running email subscribed search script ...\n";
	echo date('d/m/Y H:i:s') . "\n";
	
	$alerts = Favourites::savedSearches();

	
	// Step through each of the Closed (but non-synched) Eventum jobs
	foreach ($alerts as $alert) {
        $parsed_url = parse_url($alert['fvs_search_parameters']);
        $path = isset($parsed_url['path']) ? trim($parsed_url['path'], ' /\\') : '' ;

        $query = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        parse_str(parse_url($query, PHP_URL_QUERY), $params);

        //check if it is an author username
        $authorDetails = Author::getDetailsByUsername($path);
        if (count($authorDetails) != 0 && is_numeric($authorDetails['aut_id'])) {
            $params['browse'] = 'mypubs';
            $params['author_id'] = $authorDetails['aut_id'];
        } else{
            //Lets see if it's a saved search and also merge with get parameters (Since user might reorder saved search
             $searchAlias = Favourites::getSearchAliasURL($path);
             if ($searchAlias) {
                  //Puts the saved get values into a array
                  parse_str(parse_url($searchAlias, PHP_URL_QUERY), $savedParams);
                  $params = array_merge($savedParams, $params);
              }
        }
        $params[search_keys][0] .= " updated_date:[".$alert[fvs_most_recent_item_date]."Z TO *]";
        //print_r($params);


        //$uri .= isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
       //echo  "------------------------".$alert[fvs_search_parameters]."-------------------------";
       $search = Lister::getList($params, false);
       if ($search[list_info][total_rows] > 0) {
           //echo $parsed_url." has ".$search[list_info][total_rows];
          // Favourites::updateRecentItemDateSearch($alert[fvs_id]);
           $link = $parsed_url['host'].$parsed_url['path']."?".http_build_query( $params );
           emailUser($link, $alert[usr_email], $alert[fvs_description] );
       }
	}

	echo "Email Alerts complete.\n\n";
	
	exit;
}

function emailUser($link, $userEmail, $description="" ) {
    // Send the email.
    //$usrDetails = User::getDetails($username);
    $body = "There have been updates to the search you have saved and requested updates on. Please click here to see any updated records since your last alert\n\n";
    $body .= $description."-\n";
    $body .= $link;
    $body .= "\n\nPlease click here to unsubscribe to updates and to manage them\n";
    $body .= "https://".APP_HOSTNAME."/search_favourites.php\n";

    $mail = new Mail_API;
    $subject = "Search update alert";
    $to = $userEmail;
    $from = APP_EMAIL_SYSTEM_FROM_ADDRESS;
    echo $body;
    $mail->setTextBody(stripslashes($body));
    $mail->send($from, $to, $subject, false);
}
