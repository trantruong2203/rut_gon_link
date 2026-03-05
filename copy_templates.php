<?php
foreach(glob("/var/www/adlinkfly/plugins/*/templates/*") as $d) {
    $lower = strtolower($d);
    if(!file_exists($lower) && is_dir($d)) {
        exec("cp -r \"$d\" \"$lower\"");
    }
}
echo "Done";
