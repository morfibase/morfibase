
# MorfiBase

A lightweight open source no-code platform to create internal tools and custom databases for any business.

> [!WARNING]  
> MorfiBase is under active development, so you might encounter bugs and compatibility issues when upgrading. We plan to keep version 0.x until we are confident in delivering a fully stable, battle-tested app with version 1.x


## Features
- Easy to self-host
- Lightweight, portable, no-code, zero-setup database (SQLite)
- Table relationships
- Built on top of Laravel and FilamentPHP
- Modern UI


## Run Locally

### What do you need in order to run the app?
- PHP 8.2+
- Composer
- Node.js version 20.19+ or 22.12+

### Running the app

Clone the project

```bash
  git clone https://github.com/morfibase/morfibase.git
```

Go to the project directory

```bash
  cd morfibase
```

Install dependencies


```bash
  composer install
```

```bash
  npm install
```

Start the server

```bash
  php artisan serve
```

```bash
  npm run dev
```

Installing the app

```bash
  php artisan morfibase:install
```

Then follow the instructions displayed in the terminal.

