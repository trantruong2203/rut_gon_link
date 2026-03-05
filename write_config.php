<?php
// Write adlinkfly.conf
file_put_contents("/etc/nginx/conf.d/adlinkfly.conf", base64_decode("c2VydmVyIHsKICAgIGxpc3RlbiA4MDsKICAgIHNlcnZlcl9uYW1lIGFkbGlua2ZseS5vbmxpbmUgd3d3LmFkbGlua2ZseS5vbmxpbmUgMTAzLjEzNy4xODUuNzg7CiAgICByb290IC92YXIvd3d3L2FkbGlua2ZseS93ZWJyb290OwogICAgaW5kZXggaW5kZXgucGhwOwoKICAgIGFjY2Vzc19sb2cgL3Zhci9sb2cvbmdpbngvYWRsaW5rZmx5LWFjY2Vzcy5sb2c7CiAgICBlcnJvcl9sb2cgL3Zhci9sb2cvbmdpbngvYWRsaW5rZmx5LWVycm9yLmxvZzsKCiAgICBsb2NhdGlvbiAvIHsKICAgICAgICB0cnlfZmlsZXMgJHVyaSAkdXJpLyAvaW5kZXgucGhwPyRhcmdzOwogICAgfQoKICAgIGxvY2F0aW9uIH4gXC5waHAkIHsKICAgICAgICBmYXN0Y2dpX3Bhc3MgMTI3LjAuMC4xOjkwMDA7CiAgICAgICAgZmFzdGNnaV9pbmRleCBpbmRleC5waHA7CiAgICAgICAgZmFzdGNnaV9wYXJhbSBTQ1JJUFRfRklMRU5BTUUgJGRvY3VtZW50X3Jvb3QkZmFzdGNnaV9zY3JpcHRfbmFtZTsKICAgICAgICBpbmNsdWRlIGZhc3RjZ2lfcGFyYW1zOwogICAgfQoKICAgIGxvY2F0aW9uIH4gL1wuIHsKICAgICAgICBkZW55IGFsbDsKICAgIH0KCiAgICBjbGllbnRfbWF4X2JvZHlfc2l6ZSAxME07Cn0K"));

// Remove default server block from nginx.conf
$content = file_get_contents("/etc/nginx/nginx.conf");
$content = str_replace("    server {
        listen       80;
        listen       [::]:80;
        server_name  _;
        root         /usr/share/nginx/html;

        # Load configuration files for the default server block.
        include /etc/nginx/default.d/*.conf;

        error_page 404 /404.html;
        location = /404.html {
        }

        error_page 500 502 503 504 /50x.html;
        location = /50x.html {
        }
    }
", "", $content);
file_put_contents("/etc/nginx/nginx.conf", $content);
echo "Done";
