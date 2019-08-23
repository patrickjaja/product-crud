# Symfony Forms in Detail

Repository contains example application used for "Symfony Forms in Detail" at [Web Summer Camp 2019](https://2019.websummercamp.com/)

## Requirements

* PHP >= 7.1.3
* [Doctrine compatible](https://www.doctrine-project.org/projects/doctrine-dbal/en/2.9/reference/introduction.html#introduction) database layer, eg. SQlite

or alternatively you could use the VirtualBox image provided by websc organizers, see Slack or email

* VirtualBox 6.0.10
* VirtualBox Extension Pack

## Setting up using WSC image

* Import & boot virtual maching

**Update repository & Build**

```bash
$ cd /var/www/html/symfony/forms
$ git pull
$ composer install
```

**Webserver (Symfony CLI)**

```bash
$ symfony serve -d
```

and open homepage (https://localhost:8000)

## Setting up w/o WSC image

**Checkout & Build** 

```bash
$ git clone git@github.com:chr-hertel/product-crud.git
$ cd product-crud
$ composer install
```

**Webserver**

Configure your vhost root to point to `public/` or use  

```bash
$ bin/console server:start
```

and open homepage (eg http://localhost:8000)

## Database

```bash
$ bin/console doctrine:database:create
$ bin/console doctrine:schema:create --force
```

## Quality Checks

You can execute the configured quality checks by running

```bash
$ bin/check
```

It will execute:

* Symfony Yaml- and Twig-Linting
* Doctrine Schema Validation
* Composer Validation
* PHPStan Static Code Analysis
* PHP-CS-Fixer Code Style
* Security Checker
* PHPUnit Testing
