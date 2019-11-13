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

## Getting Started

Testify is a school project where we need to design a complete system for training centres and driving schools to enable them to perform psychomotor tests.

This repository contains the files of the entire IT part of the project, i.e. the website used by the examination subjects, examiners and those in charge of the centre or driving school.

The website must be created with PHP without any framework. Only the JQuery library for the frontend is allowed. We chose to develop the project under PHP 7.+ under an MVC architecture with a RestFull API.

### Prerequisites

To make this project work you will need:

* PHP 7.+
* A MySQL database server
* A web server like Apache
* The [XDebug](https://xdebug.org/) extension if you want to run unit tests (PHPUnit is provided in PHAR format in `tools/` folder)

### Installing

* Put all the files in your site directory
* On your MySQL server create a database called `testify`. Import the `tools/testify.sql` script into your MySQL server to get the different tables needed by the project.
* Everything is ready you can now access your site! (usually locally: http://localhost/appsy_project)

### Running the tests

To run PHPUnit on the unit tests in the `tests/` folder, use the following command:
```bash
$ php tools/phpunit.phar tests
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
