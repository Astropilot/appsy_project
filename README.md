<h1 align="center">
  <br>
  <img src="https://raw.githubusercontent.com/Astropilot/appsy_project/master/public/images/logo_testify.png" alt="Testify" width="200">
</h1>

<h4 align="center">
A school project to propose a complete system for psychomotor testing</h4>

<p align="center">
  <a href="https://travis-ci.org/Astropilot/appsy_project">
    <img src="https://travis-ci.org/Astropilot/appsy_project.svg?branch=master"
         alt="Build Status">
  </a>
  <a href="https://codecov.io/gh/Astropilot/appsy_project"><img src="https://codecov.io/gh/Astropilot/appsy_project/branch/master/graph/badge.svg" alt="Code Covegarde"></a>
  <img src="https://img.shields.io/badge/Made%20with-%E2%9D%A4%EF%B8%8F-yellow.svg">
</p>

<p align="center">
  <a href="#about">About</a> •
  <a href="#getting-started">Getting Started</a> •
  <a href="#download">Download</a> •
  <a href="#authors">Authors</a> •
  <a href="#license">License</a>
</p>

## About

Testify is a school project where we need to design a complete system for training centres and driving schools to enable them to perform psychomotor tests.

This repository contains the files of the entire IT part of the project, i.e. the website used by the examination subjects, examiners and those in charge of the centre or driving school.

The website must be created with PHP without any framework. Only the JQuery library for the frontend is allowed. We chose to develop the project under PHP 7.+ under an MVC architecture with a RestFull API.

## Getting Started

### Prerequisites

To make this project work you will need:

* PHP 7.+
* A MySQL database server
* A web server like Apache
* The [XDebug](https://xdebug.org/) extension if you want to run unit tests (PHPUnit is provided in PHAR format in `tools/` folder)

### Installing

* Put all the files in your site directory
* Configure your web server to serve the `appsy_project/public/` folder directly.

A example with Apache in your `httpd.conf` file:
```ini
DocumentRoot "path/to/appsy_project/public"
<Directory "path/to/appsy_project/public">
    Options FollowSymLinks Includes ExecCGI
    AllowOverride All
    Require all granted
</Directory>
```
* Configure your web server to declare 3 environment variables: `SMTP_HOST`, `MAIL_USERNAME` and `MAIL_PASSWORD`.
  * `SMTP_HOST`: This is the address of the mail server to communicate to. Eg: smtp.gmail.com
  * `MAIL_USERNAME`: This is the sender email address (used to send registration emails)
  * `MAIL_PASSWORD`: This is the password for authenticating the email address defined above.

A example with Apache in your `httpd.conf` file:
```ini
SetEnv SMTP_HOST smtp.gmail.com
SetEnv MAIL_USERNAME sample@gmail.com
SetEnv MAIL_PASSWORD my_password
```
* On your MySQL server create a database called `testify`. Import the `tools/testify.sql` script into your MySQL server to get the different tables needed by the project.
* Everything is ready you can now access your site!

### Utils CLI

We provide a mini PHP cli application with some util commands.
Here the list of the different commands available:
- `clear-cache` : Clear the cache within `app/cache` folder. (Basically it delete all .php and .chtml files)

### Running the tests

To run PHPUnit on the unit tests you have two different configuration files, one for the Framework tests and the other one for the Project tests. You can start them by the following commands:
```bash
$ php tools/phpunit.phar --configuration framework.xml.dist
$ php tools/phpunit.phar --configuration project.xml.dist
```

## Download

You can [download](https://github.com/Astropilot/appsy_project/releases/tag/v1.0.0) the latest installable version present in releases.

## Authors

* Elia Tso ([Github@eliazuo](https://github.com/eliazuo))
* François Chiv ([Github@Francois-chiv](https://github.com/Francois-chiv))
* Solène Goudout ([Github@Sogoud](https://github.com/Sogoud))
* Timothée Pionnier ([Github@TimPionnier](https://github.com/TimPionnier))
* Yohann Martin ([Github@Astropilot](https://github.com/Astropilot))
* Ziyad Adrar ([Github@ziyad-A](https://github.com/ziyad-A))

## Licence

MIT

---

> [isep.fr](https://www.isep.fr/) &nbsp;&middot;&nbsp;
> 2019-2020 &nbsp;&middot;&nbsp;
> Projet d'APP
