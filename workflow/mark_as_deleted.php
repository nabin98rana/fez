<?php
Record::markAsDeleted($this->pid);
// need to add history here because the status object doesn't like to add history to a deleted object
History::addHistory($this->pid, $this->wfl_details['wfl_id'], '', '', true);
?>