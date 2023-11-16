# Nix package version search

> See the frontend for this project at [RikudouSage/NixPackageHistoryFrontend](https://github.com/RikudouSage/NixPackageHistoryFrontend).

This is a backend for searching previous versions of nix packages, similar to [lazamar/nix-package-versions](https://github.com/lazamar/nix-package-versions).
Except that one stopped working for me, so I made this one.

This project consists of two main parts: updater command and a simple JSON controller.

Before you can do any of the below, you must install dependencies using `composer install`.

> Tip: If you're using nix, you can get all dependencies using `nix-shell` with the `shell.nix` from this repo

## Setting up database

Before you can do anything, you must setup the database. By default the project uses SQLite database located at
`var/database.db`.

You can change it by providing your own path by overriding the env variable `DATABASE_URL` (you can also create a file
called `.env.local` in this project and put the environment variable there).

Some examples:

```dotenv
DATABASE_URL="sqlite:///%kernel.project_dir%/var/database.db" # the default value, %kernel.project_dir% gets replaced by the path to project
DATABASE_URL="mysql://user:password@127.0.0.1:3306/database?serverVersion=8.0.32&charset=utf8mb4"
DATABASE_URL="postgresql://user:password@127.0.0.1:5432/database?serverVersion=15&charset=utf8"
```

As a next step you need to migrate the database. If you're using SQLite, you can just use the provided migrations, otherwise
you need to generate them as well:

- `./bin/console doctrine:migrations:diff` (skip this if you're using SQLite)
- `./bin/console doctrine:migrations:migrate -n`

> Tip: IF you want to use the database used at the hosted version, you can download it from [database/database.db](database/database.db).

## Updater

The updater parses information from the official [nixpkgs repo](https://github.com/NixOS/nixpkgs).

> You don't need to do any of the following if you only want to use the http endpoints with an existing database.

### Usage

First you need to clone the repo, it doesn't matter where you clone it:

`git clone https://github.com/NixOS/nixpkgs`

As a next step, create an environment variable `PATH_TO_NIXPKGS` and use the path to the repo as a value
(you can also create a file called `.env.local` in this project and put the environment variable there).

```dotenv
PATH_TO_NIXPKGS=/home/my-username/some/path/to/nixpkgs
```

Afterwards, you can run the command:

- `./bin/console app:parse-packages`

Without any parameters it will parse every single commit which might get a little noisy (and very slow).

You can customize the behavior using these parameters:

- `--start-at <commit-ref>` - the commit reference to start at, defaults to `HEAD`
- `--skip` - the amount of days to skip when parsing, meaning it will parse packages from a commit only if it's older than
  the specified amount of days compared to the last imported commit
- `--stop-at` - the date and time that is the oldest allowed for import

The command will traverse all available commits and extract package name and version, along with the information
about which revision contains the version. This will be stored in the database.

> Note that the `nix-env` command is used to get information about packages, meaning you need to run this on an OS with
> nix installed.

## HTTP endpoints

A set of simple endpoints for getting information from the database.

## Usage

For development you can run the dev server using `symfony server:start` command (from the `symfony-cli`).

> Tip: If you're using nix, you can get all dependencies using `nix-shell` with the `shell.nix` from this repo

For production you can use any of the available http servers, like apache with php module, or apache/nginx/caddy with
php-fpm (or any other webserver capable of communicating using fpm).

## Endpoints

For endpoint description see the provided [openapi.yaml](openapi.yaml).
