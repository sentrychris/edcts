# ED:CTS - Carrier Transport Services

Backend services for ED:CTS - responsible for communicating with Elite 3rd party services and providing data to the [ED:CTS frontend](https://github.com/sentrychris/edcts-frontend).

## Development

ED:CTS backend is built with [Laravel](https://laravel.com/) and uses [MariaDB](https://mariadb.org/) for storage.

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
        laravelsail/php82-composer:latest \
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
    DB_HOST=edcts-mariadb-1 # name of the running db container
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
    ... # the rest should be fine
    ```

4. Start the containers:
    ```sh
    ./vendor/bin/sail up -d
    ```

5. Create the database tables:
    ```sh
    ./vendor/bin/sail artisan migrate
    ```

6. Seed systems data **before** running other seeders:
    1. Download the `systemsPopulated.json` archive [from EDSM](https://www.edsm.net/dump/systemsPopulated.json.gz)
    2. Unzip it to `storage/dumps/`
    3. Run the import command:
        ```sh
        ./vendor/bin/sail artisan elite:import-galaxy-systems \
            --file systemsPopulated.json \
            --has-info
        ```

7. Seed data:
    ```sh
    ./vendor/bin/sail artisan db:seed
    ```
    - users (all with password of "_password_")
    - commanders (with fake api keys)
    - fleet carriers and scheduled fleet carrier trips

8. Seed GalNet news articles (I recommend using the JSON feed):
    ```sh
    ./vendor/bin/sail artisan elite:import-galnet-news -f json
    ```

## Swagger Documentation

To access swagger UI, run `npm run docs` and access on http://localhost:8888.

## Credits

- [AnthorNet/EDSM](https://github.com/EDSM-NET) - for the wonderful cartographical data and API.