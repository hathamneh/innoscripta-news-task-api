# Innoscripta News Task API

## Installation

### TL;DR

```shell
composer install
cp .env.example .env
php artisan key:generate # generate a new APP_KEY
./vendor/bin/sail up -d # start the app and database containers
./vendor/bin/sail artisan migrate # run the migrations
./vendor/bin/sail artisan db:seed # seed `countries` table

# Fetch news from providers, requires API keys
./vendor/bin/sail artisan app:fetch-news 
# Run the scheduler to fetch news every 15 minutes
./vendor/bin/sail artisan schedule:work
```

Below is a detailed explanation of the installation process:

### Requirements

- PHP 8.1+
- [Composer](https://getcomposer.org/)

### Dependencies

Install the dependencies using composer:

```shell
composer install
```

### Environment

Copy `.env.example` to `.env` the default values should work for a local development environment.

In order to fetch news from providers you need to set the API keys for each provider, check below for more information
about the providers.

You also need to update the `APP_KEY`, you can generate a new one using the following command:

```shell
php artisan key:generate
```

### Using Docker (Laravel Sail)

This application is using Laravel Sail to run the application in a docker container.
If you don't want to use Laravel Sail you can always use `docker-compose` directly.

This documentation assumes you are using Laravel Sail.

Run the following command to build the docker images and start the containers:

```shell
./vendor/bin/sail up -d
```

This will start the containers in the background, you can check the status of the containers using the following
commands:

```shell
./vendor/bin/sail ps # List all containers
./vendor/bin/sail logs -f # Follow logs
```

### Database

Run the migrations to create the database tables:

```shell
./vendor/bin/sail artisan migrate
```

Seed the `countries` table with the ISO 3166-1 alpha-2 codes:

```shell
./vendor/bin/sail artisan db:seed
```

### Fetching news

You can fetch news from providers using the following command:

```shell
./vendor/bin/sail artisan app:fetch-news
```

This command will fetch news from all providers, you can also specify a provider:

```shell
./vendor/bin/sail artisan app:fetch-news NewsDataIOProvider
```

### Scheduler

You can run the scheduler to fetch news from providers every 15 minutes automatically:

Locally:

```shell
./vendor/bin/sail artisan schedule:work
```

In production, you need to run the following command to modify the crontab of the OS:

```shell
./vendor/bin/sail artisan schedule:run
```

#### Supported Providers

The application supports the following providers:

- ##### NewsAPIProvider

You need to set the `NEWS_API_KEY` environment variable to your API key. You can get an API key
from [NewsAPI.org](https://newsapi.org/).

- ##### NewsDataIOProvider

You need to set the `NEWSDATAIO_API_KEY` environment variable to your API key. You can get an API key
from [NewsData.io](https://newsdata.io/).

- ##### TheGuardianProvider

You need to set the `THEGUARDIAN_API_KEY` environment variable to your API key. You can get an API key
from [The Guardian Open Platform](https://open-platform.theguardian.com/).

##### Adding a new provider

To add a new provider you need to create a new class that extends the `NewsProvider` abstract class and implement
the `articles` and `sources` methods. You can check the existing providers for examples.
