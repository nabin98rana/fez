<?php


include_once(APP_INC_PATH . "class.bgp_fulltext_index.php");


class FulltextIndex {

    var $bgp;
    var $mimetypes = array('application/pdf','text/plain');
    // ignorewords based on http://www.world-english.org/english500.htm
    // see also http://www.ranks.nl/tools/stopwords.html
    var $ignorewords = array(
            'the', 'of','to', 'and','a', 'in','is', 'it','you', 'that', 'he', 'was', 'for', 'on', 'are', 'with',
            'as', 'I', 'his', 'they', 'be', 'at', 'one', 'have', 'this', 'from', 'or', 'had', 'by', 'hot', 'but',
            'some', 'what', 'there', 'we', 'can', 'out', 'other', 'were', 'all', 'your', 'when', 'up', 'use', 'word',
            'how', 'said', 'an', 'each', 'she', 'which', 'do', 'their', 'time', 'if', 'will', 'way', 'about',
            'many', 'then', 'them', 'would', 'like', 'so', 'these', 'her', 'make',
            'see', 'him',  'has', 'look', 'more',  'could', 'go', 'come', 'did', 'my', 
            'no', 'most',  'who', 'over', 'know',  'than', 'call',   'may',
            'down', 'side', 'been', 'now', 'find', 'any', 'new', 'work', 'part', 'take', 'get',  'made',
            'where', 'after', 'back',  'only',  'man', 'year', 'came', 'show', 'every',
            'me', 'give', 'our', 'name', 'very', 'through', 'just', 'form', 'much',  'think',
            'say',   'turn', 'cause', 'same', 'mean',  'move', 'right',
            'too', 'does', 'tell',  'set', 'three', 'want', 'well', 'also', 
            'small', 'end', 'put', 'home', 'read', 'hand', 'port',  'add', 'even',  'here',
            'must', 'big',  'such',  'why', 'ask', 'men',  'went',  'kind',
            'off',  'try', 'us', 'own',  'should', 'found', 'let',  'never', 'last', 'don\'t', 'while' );
    var $pid_count = 0;


    function setBGP(&$bgp) {
        $this->bgp = &$bgp;
    }

    function indexPid($pid, $regen=false) {
       $bgp = new BackgroundProcess_Fulltext_Index; 
       $bgp->register(serialize(compact('pid','regen')), Auth::getUserID());
    }

    function indexBGP($pid, $regen=false,$topcall=true)
    {
        $this->regen = $regen;
        $this->bgp->setHeartbeat();
        $this->bgp->setProgress(++$this->pid_count);
        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX;
        $rec = new RecordGeneral($pid);

        $dslist = $rec->getDatastreams();
        if (empty($dslist)) {
            return;
        }
        foreach ($dslist as $dsitem) {
            $this->indexDS($rec,$dsitem);
        }

        // recurse children
        $children = $rec->getChildrenPids();
        if (!empty($children)) {
            $this->bgp->setStatus("Recursing into ".$rec->getTitle());
        }
        foreach ($children as $child_pid) {
            $this->indexBGP($child_pid,$regen,false);
        }
        $this->bgp->setStatus("Finished Fulltext Index for ".$rec->getTitle());
    }

    /**
      * @param array $dsitem - a ds listing item as returned from getDatastreams
      */
    function indexDS(&$rec,$dsitem) 
    {
        // determine the type of object
        switch ($dsitem['controlGroup']) {
            case 'X':
                break;
            case 'M':
                // managed means that we have a copy here
                $this->indexManaged($rec,$dsitem);
                break;
            case 'R':
                // index the remote object
                // leave this alone for now - the remote object could be html or doc or who knows what
                // there might also be ads on the target page and all sorts of things that we don't want to index
                break;
            default:
                // don't index it if it's unknown
                break;
        }

    }

    function indexManaged(&$rec, $dsitem)
    {
        $can_index = $this->checkMimeType($dsitem['MIMEType']);
        if (!$can_index) {
            return;
        }
        // get a copy of the file and put it in the temp directory
        $filename = APP_TEMP_DIR."fulltext_{$dsitem['ID']}";
        $content = &$rec->getDatastreamContents($dsitem['ID']);
        file_put_contents($filename, $content);
        $textfilename = $this->convertFile($dsitem['MIMEType'], $filename);
        unlink($filename);
        if (!empty($textfilename) && is_file($textfilename)) {
            $plaintext = file($textfilename);
            unlink($textfilename);
            // index the plaintext
            if (!empty($plaintext)) {
                $this->indexPlaintext($rec,$dsitem['ID'],$plaintext);
            }
        }
    }

    function checkMimeType($mimetype)
    {
        return in_array($mimetype, $this->mimetypes);
    }

    function convertFile($mimetype, $filename) 
    {
        $textfilename = $filename.".txt";

        // convert to plain text
        $plaintext = '';
        switch ($mimetype) {
            case 'application/pdf':
                exec(APP_PDFTOTEXT_EXEC." $filename $textfilename");
                break;
            case 'text/plain':
                $textfilename = $filename;
                break;
            default:
                // if we couldn't convert the file, then return a blank textfilename
                $textfilename = null;
                break;
        }
        return $textfilename;
    }

    function indexPlaintext(&$rec, $dsID, &$plaintext)
    {
        list($fti_id, $new_item) = $this->getItemId($rec, $dsID);
        // If the item has already been indexed, then we'll only regenerate the index if the regen flag was sent.
        // Otherwise just return from here without doing anything.
        if (!$this->regen && !$new_item) {
            return;
        }

        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX;
        $stmt = "DELETE FROM {$dbtp}fulltext_engine WHERE fte_fti_id='$fti_id' ";
        $res = $GLOBALS['db_api']->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
        }

        $words = array();
        foreach ($plaintext as $num => $line) {
            // get keywords
            $keywords = preg_split('/[^\w\d]/', $line, -1, PREG_SPLIT_NO_EMPTY);

            // ignorewords
            $ignore_words = &$this->getIgnoreWords();
            foreach ($keywords as $word) {
                $word = trim(strtolower($word));
                // skip anything less than three letters
                if (strlen($word) < 3) {
                    continue;
                }
                // skip numbers
                if (is_numeric($word)) {
                    continue;
                }
                // skip ignore words
                if (in_array($word, $ignore_words)) {
                    continue;
                }
                // count occurances
                if (!isset($words[$word])) {
                    $words[$word] = 1;
                } else {
                    $words[$word]++;
                }
            }
        }
        foreach ($words as $word => $weight) {
            // get a key_id for the word
            $key_id = $this->getKeyId($word);

            // associate words with pid
            $stmt = "INSERT INTO ".$dbtp."fulltext_engine (fte_fti_id,fte_key_id,fte_weight) 
                VALUES ('".$fti_id."','".$key_id."','".$weight."') ";
            $res = $GLOBALS['db_api']->dbh->query($stmt);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            }
        }
    }

    function getIgnoreWords()
    {
        return $this->ignorewords;
    }

    function getKeyId($word) 
    {
        $word = substr($word, 0, 64); // limit the word to the length of the field in the DB
        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX;
        $stmt = "SELECT ftk_id FROM ".$dbtp."fulltext_keywords WHERE ftk_word = '".Misc::escapeString($word)."'";
        $res = $GLOBALS['db_api']->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            $res = 0;
        }
        if ($res > 0) {
            $key_id = $res;
        } else {
            $stmt = "INSERT INTO ".$dbtp."fulltext_keywords (ftk_word, ftk_twoletters) VALUES
                ('".Misc::escapeString($word)."',
                 '".Misc::escapeString(substr(str_replace('\\','',$word),0,2))."') ";
            $res = $GLOBALS['db_api']->dbh->query($stmt);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            }
            $key_id = $GLOBALS['db_api']->get_last_insert_id();
        }
        return $key_id;
    }

    function getItemId(&$rec, $dsID) 
    {
        $pid = $rec->getPid();
        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX;
        $stmt = "SELECT * FROM ".$dbtp."fulltext_index WHERE fti_pid='".$pid."' AND fti_dsid='".$dsID."'";
        $res = $GLOBALS['db_api']->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            $res = array();
        }
        if (!empty($res)) {
            $fti_id = $res['fti_id'];
            $new_id = false;
        } else {
            $stmt = "INSERT INTO ".$dbtp."fulltext_index (fti_pid, fti_dsid, fti_indexed) 
                VALUES ('".$pid."', '".$dsID."', NOW()) ";
            $res = $GLOBALS['db_api']->dbh->query($stmt);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            }
            $fti_id = $GLOBALS['db_api']->get_last_insert_id();
            $new_id = true;
        }
        return array($fti_id, $new_id);
    }


    function getSearchJoin($fulltext_input)
    {
        $ft_stmt = '';
        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX;
        if (!empty($fulltext_input)) {
            $keywords = preg_split('/[^\w\d]/', $fulltext_input, -1, PREG_SPLIT_NO_EMPTY);

            // ignorewords
            $ignore_words = &$this->getIgnoreWords();
            $keywords = array_diff($keywords, $ignore_words);
            $keywords = array_unique($keywords);
            $numeric_words = array_filter($keywords, 'is_numeric');
            $keywords = array_values(array_diff($keywords, $numeric_words));

            if (!empty($keywords)) {
                // For AND operator, sum up the weights from each of the joins
                $ft_weight_select = '';
                foreach ($keywords as $num => $word) {
                    $ft_weight_select .= "sum(fte".$num.".fte_weight)+";
                }
                $ft_weight_select = rtrim($ft_weight_select,'+');
                $ft_stmt = "INNER JOIN (
                    SELECT fti_pid, ".$ft_weight_select." as Relevance FROM ".$dbtp."fulltext_index as fti  
                    ";
                // Use INNER JOINS to AND the keywords
                foreach ($keywords as $num => $word) {
                    $ft_stmt .= "INNER JOIN ".$dbtp."fulltext_engine as fte".$num." ON fte".$num.".fte_fti_id=fti.fti_id
                       INNER JOIN ".$dbtp."fulltext_keywords as ftk".$num." 
                       ON ftk".$num.".ftk_twoletters='".Misc::escapeString(substr(str_replace('\\','',$word),0,2))."'
                       AND ftk".$num.".ftk_word like '".Misc::escapeString($word)."%' 
                       AND ftk".$num.".ftk_id=fte".$num.".fte_key_id ";
                }
                /**
                 *      This works for an OR operator
                 *  $ft_stmt .= "INNER JOIN (
                 *   SELECT fti_pid, sum(fte.fte_weight) as Relevance FROM {$dbtp}fulltext_index as fti
                 *   INNER JOIN {$dbtp}fulltext_engine as fte ON fte.fte_fti_id=fti.fti_id
                 *   INNER JOIN ( ";        // ))
                 *  foreach ($keywords as $num => $word) {
                 *      if ($num > 0) {
                 *          $ft_stmt .= "
                 *              UNION ";
                 *      }
                 *      $ft_stmt .= "
                 *          SELECT ftk_id FROM {$dbtp}fulltext_keywords
                 *          WHERE ftk_twoletters='".Misc::escapeString(substr(str_replace('\\','',$word),0,2))."'
                 *          AND ftk_word like '".Misc::escapeString($word)."%' ";
                 *  }
                 *  $ft_stmt .= "
                 *      ) as jftk ON jftk.ftk_id=fte.fte_key_id";
                 */
                $ft_stmt .= "
                    GROUP BY fti.fti_pid
                    ) as ft1 ON ft1.fti_pid=r1.rmf_rec_pid";
            }

         }
        return $ft_stmt;
    }

    function removeByPid($pid)
    {
        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX;
        $stmt = "SELECT fti_id FROM ".$dbtp."fulltext_index WHERE fti_pid='".$pid."' ";
        $res = $GLOBALS['db_api']->dbh->getCol($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            $res = array();
        }
        if (!empty($res)) {
            $ftis = Misc::array_to_sql($res);
            $stmt = "DELETE FROM ".$dbtp."fulltext_engine WHERE fte_fti_id IN (".$ftis.") ";
            $res = $GLOBALS['db_api']->dbh->query($stmt);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                $res = null;
            }
            $stmt = "DELETE FROM ".$dbtp."fulltext_index WHERE fti_pid='".$pid."' ";
            $res = $GLOBALS['db_api']->dbh->query($stmt);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                $res = null;
            }
        }
    }
    
    function removeByDS($pid,$dsID)
    {
        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX;
        $stmt = "SELECT fti_id FROM ".$dbtp."fulltext_index WHERE fti_pid='".$pid."' AND fti_dsid='".$dsID."' ";
        $res = $GLOBALS['db_api']->dbh->getCol($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            $res = array();
        }
        if (!empty($res)) {
            $ftis = Misc::array_to_sql($res);
            $stmt = "DELETE FROM ".$dbtp."fulltext_engine WHERE fte_fti_id IN (".$ftis.") ";
            $res = $GLOBALS['db_api']->dbh->query($stmt);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                $res = null;
            }
            $stmt = "DELETE FROM ".$dbtp."fulltext_index WHERE fti_pid='".$pid."'  AND fti_dsid='".$dsID."' ";
            $res = $GLOBALS['db_api']->dbh->query($stmt);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                $res = null;
            }
        }
    }

}


?>
