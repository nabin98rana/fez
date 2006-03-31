<?php

/**
  * Class to help construct a CSV file
  */

class CSV_Array {

    var $columns=array();
    var $rows=array();
    var $currentRow=-1;
    
    function CSV_Array()
    {
    }

    /**
      * Add a column heading to the CSV file.  Only columns added here will be printed.
      * Usually addValue calls this so no need to call it from user code.
      */
    function addColumn($colname)
    {
        if (!in_array($colname,$this->columns)) {
            $this->columns[] = $colname;
        }
    }
    
    function getInheritedRow($row) 
    {
        if (!is_numeric($row['__options']['inherit'])) {
            return false;
        }
        return $row['__options']['inherit'];
    }

    /**
      * Used by toCSV to repeat rows where only a few values have changed.
      * Rows marked to inherit will get values from the inherited rows if the values in the current row are
      * blank.
      */
    function getInheritedValue($row, $col)
    {
        $irow = $this->getInheritedRow($row);
        if (!is_null(@$this->rows[$irow][$col])) {
            return $this->rows[$irow][$col];
        } else {
            return '';
        }
    }

    /**
      * Output as a CSV string ready to write to a file or stream to browser.
      */
    function toCSV()
    {
        $csv = '';
        foreach ($this->columns as $col) {
            $csv .= "\"$col\",";
        }
        $csv = rtrim($csv,',')."\n";
        foreach ($this->rows as $row) {
            foreach ($this->columns as $col) {
                if (isset($row[$col])) {
                    $value = $row[$col];
                } elseif ($this->getInheritedRow($row) !== false) { 
                    $value = $this->getInheritedValue($row, $col);
                } else {
                    $value = '';
                }
                $value = str_replace("\n",' ',$value);
                $value = str_replace("\r",' ',$value);
                $value = str_replace('"','""',$value);
                $csv .= "\"{$value}\",";
            }
            $csv = rtrim($csv,',')."\n";
        }
        return $csv;
    }

    /**
      * Start a new row in the CSV file.  Usually called by user with no options.  The 
      * internal functions may add a row without selecting it so that value will go into the
      * previously added row.  This is used when duplicate columns are found in a row.
      */
    function addRow($select = true, $options=null)
    {
        
        $this->rows[] = array('__options' => $options);
        if ($select) {
            $this->currentRow = count($this->rows)-1;
        }
        return count($this->rows)-1;
    }

    /**
      * Adds the value to the current row at the column named by column.  Can alternatively
      * index the row directly but bear in mind that the duplicate handling will add rows 
      * so the sequence of rows may not be what is expected.
      * If there is already a value at the specified column in the current row, a duplicate row row will be 
      * searched for that has the inherit value set to the current row.  If all the duplicate rows are full for 
      * this column, then a new one is created.
      */
    function addValue($value, $column, $row = null)
    {
        if (empty($row)) {
            $row = $this->currentRow;
        }
        $this->addColumn($column);
        if (empty($this->rows[$row][$column])) {
            $this->rows[$row][$column] = $value;
        } else {
            $duprow = $this->findDupRow($row);
            $this->addValue($value,$column,$duprow);
        }
    } 

    /**
     * look for the next row that duplicates current row, if none, then create a new row
     * @param integer $row - the row being inserted into.  Pass this in because when we go recusive
     *                          we want to track if inserting into a dup row.
     */
    
    function findDupRow($row) 
    {
        for ($duprow = $row + 1;
                $duprow < count($this->rows) 
                && (!isset($this->rows[$duprow]['__options']['inherit'])
               || $this->rows[$duprow]['__options']['inherit'] != $this->currentRow ); 
                $duprow++) {
        }
        if ($duprow >= count($this->rows)) {
            $duprow = $this->addRow(false, array('inherit'=>$this->currentRow));
        }
        return $duprow;
    }

    /**
      * loops through an associative array using the keys as column names and adding the values to the current row.
      */
    function addArray($a)
    {
        foreach ($a as $key => $item) {
            if (is_array($item)) {
                if (count($item) == 1) {
                    $item = array_values($item);
                    $this->addValue($item[0],$key);
                } elseif (count($item) > 1) {
                    $this->addArrayFlatten($item,$key);
                }
            } else {
                $this->addValue($item,$key);
            }
        }

    }

    function addArrayFlatten($a, $key)
    {
        foreach ($a as $item) {
            if (is_array($item)) {
                if (count($item) == 1) {
                    $item = array_values($item);
                    $this->addValue($item[0],$key);
                } elseif (count($item) > 1) {
                    $this->addArrayFlatten($item,$key);
                }
            } else {
                $this->addValue($item,$key);
            }
        }

    }
}

?>
