<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006 The University of Queensland,               |
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
class Graphviz
{
    function getCMAPX($dot)
    {
        $tmpfname = tempnam(APP_TEMP_DIR, "espace_gv_");

        $handle = fopen($tmpfname, "w");
        fwrite($handle, $dot);
        fclose($handle);
		$return_status = 0;
		$return_array = array();
		$command = APP_DOT_EXEC." -Tcmapx ".$tmpfname;
        $result = '';
		exec($command, $result, $return_status);
		if ($return_status <> 0) {
			Error_Handler::logError("GraphViz CMAPX Error: return status = ".$return_status.", for command ".$command." \n", __FILE__,__LINE__);
			$result = "";
		} 
        unlink($tmpfname);
        return implode("\n",$result);
    }

    function getPNG($dot)
    {
        $tmpfname = tempnam(APP_TEMP_DIR, "espace_gv_");

        $handle = fopen($tmpfname, "w");
        fwrite($handle, $dot);
        fclose($handle);
		$return_status = 0;
		$command = APP_DOT_EXEC." -Tpng ".$tmpfname;
        passthru($command, $return_status);
		if ($return_status <> 0) {
			Error_Handler::logError("GraphViz PNG Error: return status = ".$return_status.", for command ".$command." \n", __FILE__,__LINE__);
		}
        unlink($tmpfname);
    }

    function getGraphName($dot)
    {
        preg_match('/(di)?graph\s+(\S+)\s*{/', $dot, $matches); // }
        return $matches[2];
    }
}

?>
