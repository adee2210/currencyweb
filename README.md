# Currency Conversion website

currencyweb use php-forex-quotes Library for fetching currency symbol and exchange rate, as the api has limit request for each key, so we add cache system to reduce request traffic.

# Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)

## Requirements
* PHP >= 7
* Composer >= 1.6
* Symfony >= 4.2
* git >= 2.19

## Installation
First, clone the files
```
git clone https://github.com/adee2210/currencyweb.git
```
Or Download ZIP file and extract in any folder on your computer

Next, go inside your website files directory by
```
cd currencyweb
```

Finally, install require files by
```
composer update
```

## Usage

### Run on your personal computer
```
php bin/console server:run
```
Open your browser and natigate to

```
http://127.0.0.1:8000
```