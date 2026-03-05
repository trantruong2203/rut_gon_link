<?php
foreach(glob("/var/www/adlinkfly/plugins/*/templates/*") as $d) {
    $lower = strtolower($d);
    if(!is_dir($lower) && is_dir($d)) {
        copy($d, $lower);
    }
}
echo "Done";
