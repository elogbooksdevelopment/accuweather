# Accuweather Proxy API

The idea of this service is to hide the Accuweather API key from the public and
to allow further restrictions, such as HTTP Origin, to be added at the server side.

## Nginx Config

```
server {
    # Use this for deployments
    listen 80 default_server;

    # Use this for local development
    server_name accuweather.local;

    root /var/www/Sixpaths/accuweather/public/;
    index app.php =404;

    location @rewrite {
        rewrite ^(.*)$ /app.php/$1 last;
    }

    location / {
        add_header Access-Control-Allow-Origin "$http_origin" always;
        add_header Access-Control-Allow-Methods "OPTIONS, GET";
        add_header Access-Control-Allow-Headers "Origin,X-Password,Authorization" always;
        add_header Access-Control-Allow-Credentials "true" always;

        try_files $uri @rewrite;
    }

    location ~ ^/app\.php(/|$) {
        include fastcgi_params;

        add_header Access-Control-Allow-Origin "$http_origin" always;
        add_header Access-Control-Allow-Methods "OPTIONS, GET";
        add_header Access-Control-Allow-Headers "Origin,X-Password,Authorization" always;
        add_header Access-Control-Allow-Credentials "true" always;

        fastcgi_pass unix:/var/run/php/php7.1-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;

        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_read_timeout 5;
    }
}
```

## Usage

Reference: https://developer.accuweather.com/apis

The <path> will be determined by the API Reference path (after the domain), for example, `locations/v1/postalcodes/search` for finding a LocationKey by Postcode

```
curl -H "X-PASSWORD <password value>" http://domain.com/<path>
```
