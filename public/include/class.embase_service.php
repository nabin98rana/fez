<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
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
// | Authors: Aaron Brown <a.brown@library.uq.edu.au>                |
// +----------------------------------------------------------------------+
//
//

/**
 * Class to handle access to the Web of Knowledge Web Services.
 *
 * @version 0.1
 * @author Aaron Brown <a.brown@library.uq.edu.au>
 *
 */

class EmbaseService
{
  private $_url = EMBASE_BASE_URL;


  /**
   * Clean user query from invalid characters that may cause error on SOAP call.
   *
   * @param string $userQuery
   * @return string Cleaned user query string.
   */
  protected function _cleanUserQuery($userQuery = null)
  {
      if (empty($userQuery) && is_null($userQuery)) {
          return "";
      }

      // Clean user query string from Ms Word special characters
      $userQuery = Fez_Misc::convertMsWordSpecialCharacters($userQuery);
      return $userQuery;
  }


  /**
   * Performs a search of records from Embase
   *
   */
  public function search($query, $id = true, $maxResults = 500, $startDate = false, $endDate = false)
  {
      $format = $id ? 'id' : 'records';
      $query .= ($startDate && $endDate ) ? ':ad%20['.$startDate.']/sd%20NOT%20['.$endDate.']/sd' : '' ;
      $url = $this->_url."/xmlgateway?action=search&maxResults=".$maxResults."&format=".$format."&search_query=".$query;
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $xml = curl_exec($ch);
      curl_close($ch);
      return $xml;
  }
}
