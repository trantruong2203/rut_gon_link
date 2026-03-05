<?php
// Copy templates from plugins to lowercase folders
foreach(glob("/var/www/adlinkfly/plugins/*/templates/*") as $d) {
    $lower = strtolower($d);
    if(!file_exists($lower) && is_dir($d)) {
        exec("cp -r \"$d\" \"$lower\"");
    }
}

// Copy element/Flash to element/flash (case sensitive fix for Linux)
$srcFlash = "/var/www/adlinkfly/templates/Element/Flash";
$dstFlash = "/var/www/adlinkfly/templates/element/flash";
if (is_dir($srcFlash) && !is_dir($dstFlash)) {
    exec("cp -r \"$srcFlash\" \"$dstFlash\"");
    chown($dstFlash, 'nginx');
}
echo "Done";
