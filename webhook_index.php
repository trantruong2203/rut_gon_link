<?php
$secret = "rutgonlink123";
$payload = file_get_contents("php://input");
$signature = $_SERVER["HTTP_X_HUB_SIGNATURE_256"] ?? "";

if ($signature) {
    $hash = "sha256=" . hash_hmac("sha256", $payload, $secret);
    if (hash_equals($hash, $signature)) {
        chdir("/var/www/adlinkfly");
        
        // Create necessary directories if they don't exist
        if (!is_dir("tmp/cache/models")) mkdir("tmp/cache/models", 0777, true);
        if (!is_dir("tmp/cache/persistent")) mkdir("tmp/cache/persistent", 0777, true);
        if (!is_dir("tmp/cache/views")) mkdir("tmp/cache/views", 0777, true);
        
        // Git pull
        $output = shell_exec("git pull origin main 2>&1");
        
        // Clear cache
        shell_exec("rm -rf tmp/cache/*");
        
        // Recreate directories
        if (!is_dir("tmp/cache/models")) mkdir("tmp/cache/models", 0777, true);
        if (!is_dir("tmp/cache/persistent")) mkdir("tmp/cache/persistent", 0777, true);
        if (!is_dir("tmp/cache/views")) mkdir("tmp/cache/views", 0777, true);
        
        // Fix permissions
        shell_exec("chmod -R 777 tmp logs webroot");
        shell_exec("chown -R nginx:nginx tmp");
        
        $output .= "\nPermissions fixed, cache cleared";
        
        @file_put_contents("/var/log/deploy.log", date("Y-m-d H:i:s") . " - Deploy triggered\n$output\n", FILE_APPEND);
        echo json_encode(["success" => true, "output" => $output]);
    } else {
        http_response_code(403);
        echo json_encode(["error" => "Invalid signature"]);
    }
} else {
    echo json_encode(["status" => "waiting for webhook"]);
}
