<?php

class Graphviz
{
    function getCMAPX($dot)
    {
        $tmpfname = tempnam("/tmp", "espace_gv_");

        $handle = fopen($tmpfname, "w");
        fwrite($handle, $dot);
        fclose($handle);

        $result = shell_exec("dot -Tcmapx $tmpfname");
        unlink($tmpfname);
        return $result;


    }

    function getPNG($dot)
    {
        $tmpfname = tempnam("/tmp", "espace_gv_");

        $handle = fopen($tmpfname, "w");
        fwrite($handle, $dot);
        fclose($handle);

        passthru("dot -Tpng $tmpfname");
        unlink($tmpfname);

    }
}

?>
