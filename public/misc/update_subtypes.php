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
// | Authors: Andrew Martlew <a.martlew@library.uq.edu.au>                |
// +----------------------------------------------------------------------+

/*
 * One-off mapping script for new subtypes
 */

include_once('../config.inc.php');
include_once(APP_INC_PATH . "class.record.php");

$filter = array();
$filter["searchKey".Search_Key::getID("Object Type")] = 3; // records only
// Only Book, Book Chapter and Journal Articles
$filter["searchKey".Search_Key::getID("Display Type")] = array();
$filter["searchKey".Search_Key::getID("Display Type")]['override_op'] = 'OR';
$filter["searchKey".Search_Key::getID("Display Type")][] =
    XSD_Display::getXDIS_IDByTitleVersion('Book', 'MODS 1.0');
$filter["searchKey".Search_Key::getID("Display Type")][] =
    XSD_Display::getXDIS_IDByTitleVersion('Book Chapter', 'MODS 1.0');
$filter["searchKey".Search_Key::getID("Display Type")][] =
    XSD_Display::getXDIS_IDByTitleVersion('Journal Article', 'MODS 1.0');
$filter["searchKey".Search_Key::getID("Display Type")][] =
    XSD_Display::getXDIS_IDByTitleVersion('Conference Paper', 'MODS 1.0');
$filter["manualFilter"] = "subtype_t:[* TO *]"; // records with subtypes only

$page_rows = 100;
$listing = Record::getListing(array(), array(9,10), 0, $page_rows, 'Created Date', false, false, $filter);

$mapping = array(
    'Book' => array(
        'New ideas or perspectives based on established research finding' => 'Research book (original research)',
        'Critical scholarly text' => 'Research book (original research)',
        'Critical scholarly texts' => 'Research book (original research)',
        'New interpretations of historical events' => 'Research book (original research)',
        'Revision or new edition' => array(
            'A1' => 'Research book (original research)',
            'AX' => 'Other'
        ),
        'Non-fiction' => array(
            'A1' => 'Research book (original research)',
            'A3' => 'Edited book',
            'AX' => 'Other',
        ),
        'Fiction' => 'Creative work',
        'Textbooks'  => 'Textbook',
        'Reference' => 'Reference work, encyclopaedia, manual or handbook',
        'Edited books' => 'Edited book',
        'Anthologies'  => 'Other',
        'Other' => 'Other',
    ),
    'Book Chapter' => array(
        'Chapter in a textbook, anthology, reference book' => array(
            'B1' => 'Research book chapter (original research)',
            'BX' => 'Chapter in textbook',
        ),
        'Critical scholarly text'  => 'Research book chapter (original research)',
        'Non-fiction' => array(
            'B1' => 'Research book chapter (original research)',
            'BX' => 'Other',
        ),
        'Fiction' => 'Creative work',
        'Revision of a chapter in an edited work' => array(
            'B1' => 'Research book chapter (original research)',
            'BX' => 'Other',
        ),
        'Critical review of current research'  => 'Critical review of research, literature review, critical commentary',
        'Reference'  => array(
            'B1' => 'Research book chapter (original research)',
            'BX' => 'Chapter in reference work, encyclopaedia, manual or handbook',
        ),
        'Scholarly introduction to an edited work' => 'Introduction, foreword, editorial or appendix',
        'Forewords, brief introductions, editorials or appendices'  => 'Introduction, foreword, editorial or appendix',
        'Other'  => 'Other',
    ),
    'Journal Article' => array(
        'Article' => 'Article (original research)',
        'Review of research - research literature review (NOT book review)' => 'Critical review of research, literature review, critical commentary',
        'Review of research - research literature review (NOT book review' => 'Critical review of research, literature review, critical commentary',
        'Letter' => 'Letter to editor, brief commentary or brief communication',
        'Review of Book, Film, TV, video, software, performance, music etc' => 'Review of book, film, TV, video, software, performance, music etc',
        'Review of Book, Film, TV, video, software, performance, music et' => 'Review of book, film, TV, video, software, performance, music etc',
        //'Editorial' => 'Editorial', (unchanged)
        'Discussion – (responses, round table/ panel discussion, Q&A, reply)' => 'Discussion – responses, round table/panel discussions, Q&A, reply',
        'Discussion – (responses, round table/ panel discussion, Q&amp;A, reply)' => 'Discussion – responses, round table/panel discussions, Q&A, reply',
        'Creative output (poetry, musical score, fiction or prose)' => 'Creative work',
        'Other (News item, press release, note, obituary, other not listed)' => 'Other',
        'Other (News item, press release, note, obituary, other not liste' => 'Other',
        //'Correction/erratum' => 'Correction/erratum', (unchanged)
        'Journal - editorial role' => '',
    )
);

for ($i = 0; $i < ((int)$listing['info']['total_pages']+1); $i++) {

    // Skip first loop - we have called getListing once already
    if ($i > 0) {
        $listing = Record::getListing(
            array(), array(9,10), $listing['info']['next_page'], $page_rows, 'Created Date', false, false, $filter
        );
    }

    if (is_array($listing['list'])) {
        for ($j=0; $j < count($listing['list']); $j++) {
            $record = $listing['list'][$j];
            if (
                (! empty($record['rek_subtype'])) ||
                $record['rek_genre'] == 'Conference Paper'
            ) {
                $subtype = false;
                if (
                    array_key_exists($record['rek_genre'], $mapping) &&
                    array_key_exists($record['rek_subtype'], $mapping[$record['rek_genre']])
                ) {
                    print $record['rek_pid'] ." - " . $record['rek_genre'] . " - " .
                        $record['rek_herdc_code_lookup'] . " - " . $record['rek_subtype'];

                    $m = $mapping[$record['rek_genre']][$record['rek_subtype']];
                    if (is_array($m)) {
                        // Use HERDC code
                        if (array_key_exists($record['rek_herdc_code_lookup'], $m)) {
                            $subtype = $m[$record['rek_herdc_code_lookup']];
                        } else {
                            print "- Mapping for HERDC code not found";
                        }
                    } else {
                        $subtype = $m;
                    }
                } else if ($record['rek_genre'] == 'Conference Paper') {
                    // Copy rek_genre_type into rek_subtype
                    print $record['rek_pid'] ." - " . $record['rek_genre'] . " - " .
                        $record['rek_genre_type'];
                    $subtype = $record['rek_genre_type'];
                }

                if ($subtype) {
                    $history = 'Mapped to new subtype';
                    $r = new RecordObject($record['rek_pid']);
                    $r->addSearchKeyValueList(array("Subtype"), array($subtype), true, $history);
                    if ( APP_SOLR_INDEXER == "ON" ) {
                        FulltextQueue::singleton()->add($record['rek_pid']);
                    }
                    if (APP_FILECACHE == "ON") {
                        $cache = new fileCache($record['rek_pid'], 'pid='.$record['rek_pid']);
                        $cache->poisonCache();
                    }
                    print " - Updated with subtype: " .$subtype . "\n";
                }
            }
        }

        if ( APP_SOLR_INDEXER == "ON" ) {
            FulltextQueue::singleton()->commit();
        }
    }
}
