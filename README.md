# ED:CTS - Carrier Transport Services

Backend services for ED:CTS - responsible for communicating with Elite 3rd party services and providing data to the [ED:CTS frontend](https://github.com/sentrychris/edcts-frontend).

## Requirements

- Nginx or Apache:
    - (If using Nginx): php-fpm
    - (If using Apache): mod_php
- PHP 8.3 with extensions:
    - Ctype
    - cURL
    - DOM
    - Fileinfo
    - Filter
    - Hash
    - Mbstring
    - OpenSSL
    - PCRE
    - PDO
    - Session
    - Tokenizer
    - XML
    - Zip
    - ZMQ
- MySQL
- Redis
- Supervisor

## Development

ED:CTS backend is built with [Laravel](https://laravel.com/) and uses [MySQL](https://mysql.org/) for storage, [Redis](https://redis.io/) for caching, and [Supervisor](http://supervisord.org/) for managing queue workers and long-running artisan commands.

[Docker](https://www.docker.com/) is used for local development.

### Getting Started

1. Clone this repository:

    ```sh
    git clone git@gitub.com:sentrychris/edcts.git
    ```

2. Install dependencies:

    ```sh
    docker run --rm \
         -u "$(id -u):$(id -g)" \
         -v "$(pwd):/var/www/html" \
         -w /var/www/html \
         laravelsail/php83-composer:latest \
         composer install --ignore-platform-reqs
    ```

3. Configure environment variables:

    ```sh
    APP_NAME=EDCTS
    APP_ENV=local
    APP_KEY=base64:7Ca9S1ZfbKZlUM4GFNtUAuhQXjlwb/fKBf+wi9YW28o=
    APP_DEBUG=true
    APP_URL=http://localhost
    APP_SERVICE=web

    LOG_CHANNEL=stack
    LOG_DEPRECATIONS_CHANNEL=null
    LOG_LEVEL=debug

    DB_CONNECTION=mysql
    DB_HOST=edcts-mysql-1 # name of the running db container
    DB_PORT=3306
    DB_DATABASE=edcts
    DB_USERNAME=sail
    DB_PASSWORD=password

    BROADCAST_DRIVER=log
    CACHE_DRIVER=database
    FILESYSTEM_DISK=local
    QUEUE_CONNECTION=database
    SESSION_DRIVER=database
    SESSION_LIFETIME=120
    
    MEMCACHED_HOST=127.0.0.1

    REDIS_HOST=edcts-redis-1
    REDIS_PASSWORD=null
    REDIS_PORT=6379

    REVERB_APP_ID=appid
    REVERB_APP_KEY=key
    REVERB_APP_SECRET=secret
    REVERB_HOST="localhost"
    REVERB_PORT=8080
    REVERB_SCHEME=http
    ... # the rest should be fine
    ```

4. Start the containers:

    ```sh
    ./vendor/bin/sail up -d
    ```

5. Create the database tables:

    ```sh
    ./vendor/bin/sail artisan migrate:fresh
    ```

6. Seed populated systems data **before** running other seeders:

    1. Download the `systemsPopulated.json.gz` dump archive [from EDSM](https://www.edsm.net/dump/systemsPopulated.json.gz)
    2. Extract it to `storage/dumps/`
    3. Run the import command:
        ```sh
        ./vendor/bin/sail artisan edcts:import:dumpfile \
            --type systems \
            --channel import:system \
            --file systemsPopulated.json
        ```

7. Seed other test data:

    ```sh
    ./vendor/bin/sail artisan db:seed
    ```

    - users (all with password of "_password_")
    - commanders (with fake api keys)
    - fleet carriers and scheduled fleet carrier journeys

8. Seed Galnet news articles, the JSON feed is the default, but you can also retrieve data from the RSS feed:

    ```sh
    ./vendor/bin/sail artisan edcts:import:galnet
    ```

9. Cache system statistics:

    ```sh
    ./vendor/bin/sail artisan edcts:stats:refresh
    ```

10. Start the artisan scheduler and queue:
    ```sh
    ./vendor/bin/sail artisan schedule:work
    ./vendor/bin/sail artisan queue:work --daemon
    ```

### Credits

ED:CTS wouldn't be possible without the work of hundreds of talented members of the Elite: Dangerous community.

_"Standing on the shoulders of giants"_.

Special thanks to:


- [ED:CD](https://edcd.github.io/)  - for all of their projects, data, guidance and more.
- [EDSM](https://github.com/EDSM-NET) - for the wonderful data and API.
- [Spansh](https://www.spansh.co.uk) - for the wonderful data and API.

### Legal

"Elite", the Elite logo, the Elite: Dangerous logo, "Frontier" and the Frontier logo are registered trademarks of Frontier Developments plc. All rights reserved. All other trademarks and copyrights are acknowledged as the property of their respective owners.

ED:CTS is free, open source software released under the MIT License.
