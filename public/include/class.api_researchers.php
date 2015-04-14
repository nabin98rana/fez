<?php
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006, 2007 The University of Queensland,         |
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
// | Authors: Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>,       |
// |          Matthew Smith <m.smith@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+
//
//

/**
 * Class to handle researcher API stuff
 *
 * @version 1.0
 * @author Aaron Brown <a.brown@library.uq.edu.au>
 */
class api_researchers
{
    public static function wosCitationURL($wosArticleId)
    {
        $app_link_prefix = (defined('APP_LINK_PREFIX')) ? APP_LINK_PREFIX : '';
        $wokUsername = (defined('WOK_USERNAME')) ? WOK_USERNAME : '';
        return $app_link_prefix . "http://gateway.isiknowledge.com/gateway/Gateway.cgi?GWVersion=2&SrcApp=resolve1&DestLinkType=CitingArticles&DestApp=WOS_CPL&KeyUT=" . $wosArticleId . "&SrcAuth=" . $wokUsername;
    }

    public static function scopusCitationURL($scopusArticleId)
    {
        $app_link_prefix = (defined('APP_LINK_PREFIX')) ? APP_LINK_PREFIX : '';
        return $app_link_prefix . "http://www.scopus.com/results/citedbyresults.url?sort=plf-f&cite=" . $scopusArticleId . "&src=s&sot=cite&sdt=a";
    }

    public static function wosURL($wosId)
    {
        $app_link_prefix = (defined('APP_LINK_PREFIX')) ? APP_LINK_PREFIX : '';
        $wokUsername = (defined('WOK_USERNAME')) ? WOK_USERNAME : '';
        return $app_link_prefix . "http://gateway.isiknowledge.com/gateway/Gateway.cgi?GWVersion=2&SrcApp=resolve1&DestLinkType=FullRecord&DestApp=WOS_CPL&KeyUT=" . $wosId . "&SrcAuth=" . $wokUsername;
    }

    public static function scopusURL($scopusArticleId)
    {
        $app_link_prefix = (defined('APP_LINK_PREFIX')) ? APP_LINK_PREFIX : '';
        return $app_link_prefix . "http://www.scopus.com/record/display.url?eid=" . $scopusArticleId . "&origin=inward";
    }

    public static function googleScholar($title)
    {
        return "http://scholar.google.com/scholar?q=intitle:" . $title;
    }

    public static function altmetric($altmetricDOI)
    {
        return "http://www.altmetric.com/details.php?citation_id=" . $altmetricDOI;
    }
}
