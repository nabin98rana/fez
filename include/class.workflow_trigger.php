<?php

/**
  * WorkflowTrigger
  * Defines when workflows are triggered
  */
class WorkflowTrigger
{

    
    function getTriggerTypes()
    {
        return array(
            0 => "Ingest",
            1 => "Update", 
            3 => "Delete");
    }

    
}

?>
