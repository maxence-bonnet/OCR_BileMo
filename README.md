# <p align="center">BileMo</p>
<p align="center">Project 7 of the PHP / Symfony application developer course at OpenClassrooms</p>

## Requirements

- [MariaDB 10.4.0+](https://go.mariadb.com/) or [MySQL 5.7.0+](https://www.mysql.com/)

- [PHP 8.0.0+](https://www.php.net/) 

- [Composer 2.1+](https://getcomposer.org/) 

- [Symfony 5.3.12+](https://symfony.com/)

- [OpenSSL 1.1.1h+](https://www.openssl.org/)

---

#### For practical reasons, I chose to use :

- [XAMPP 8.0.3](https://www.apachefriends.org/fr/index.html) -> manage the database more easily (on/off + phpMyAdmin)

- [Symfony cli 4.26.10](https://symfony.com/download) -> more command lines + improved local web server

---

## Significant Bundles used
<div align="center">
  <table>
    <tr>
      <td>
        <ul>
          <li><a href="https://api-platform.com/" target="_blank">API Platform 2.6</a></li>
          <li> <a href="https://github.com/lexik/LexikJWTAuthenticationBundle" target="_blank">Lexik JWT Authentication Bundle 2.14</a></li>
          <li><a href="https://github.com/markitosgv/JWTRefreshTokenBundle" target="_blank">Gesdinet JWT Refresh Token Bundle 1.0</a></li>
        </ul>
      </td>
      <td>
       <a href="https://api-platform.com/" target="_blank">
        <img src="https://api-platform.com/static/2a15225e1eb2d831b3a11e23b5d5ed7d/Logo_Circle%20webby%20text%20blue.svg" width="200" alt="api platform logo">
        </a>
      </td>
    </tr> 
  </table>
</div>

## Install (local developpement purpose)

### 1. Clone the repository

```
git clone https://github.com/maxence-bonnet/OCR_BileMo.git
```

or [`download .zip`](https://github.com/maxence-bonnet/OCR_BileMo/archive/refs/heads/master.zip) in case you don't use have git installed

---

### 2. Install depencies via composer

In project folder :

```
composer install
```

---

### 3. While composer is running, configure the environment

Update `.env` file or create a new `.env.local` file and override / write + fill in these lines : 

```env
###> doctrine/doctrine-bundle ###
# DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=5.7"
DATABASE_URL="postgresql://symfony:ChangeMe@127.0.0.1:5432/app?serverVersion=13&charset=utf8"
###< doctrine/doctrine-bundle ###
```
Do not forget to encode special characters

#### here is a schema + example for the `.env.local` :

```env
###> doctrine/doctrine-bundle ###
DATABASE_URL="mysql://my_user_identifier:my_user_pass@127.0.0.1:3306/my_db_name?serverVersion=my_db_version"
# exemple : DATABASE_URL="mysql://root:@127.0.0.1:3306/bilemo_api?serverVersion=mariadb-10.4.18"
###< doctrine/doctrine-bundle ###
```

Then declare the location of the public & private keys for authentication (see below)

```env 
###> lexik/jwt-authentication-bundle ###
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=mystrongpass
###< lexik/jwt-authentication-bundle ###
```
#### NB: I recommend creating the `.env.local` file (ignored in commits) rather than using the `.env` to avoid committing sensitive data

---
### 4. Generate public and private keys for JSON Web Token authentication 

This is related with [Lexik JWT Authentication](#significant-bundles-used)

```
php bin/console lexik:jwt:generate-keypair
```
If, for some reasons your system does not manage to find openssl directly from the previous command, you can manually create the keypairs with the following commands:

```
mkdir -p config/jwt
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
```

---

### 5. Generating database

you can use `symfony console` instead of `php bin/console` if you have [Symfony cli](https://symfony.com/download) installed (like I did)

#### a. Create database

```
php bin/console doctrine:database:create
```

#### b. Create tables structures from migrations

```
php bin/console doctrine:migrations:migrate
```

#### c. Get demonstration data with doctrine data fixtures (optional)

```
php bin/console doctrine:fixtures:load
```

---

### 6. Run your local server

either with :

```
php -S 127.0.0.1:8000 -t public
```

or with symfony-cli :

```
symfony server:start -d
```
-d for --daemon flag (optional) disables verbose mode and runs server in the background so you can keep using your terminal

notice that you can also [simulate TLS](https://symfony.com/doc/current/setup/symfony_server.html#enabling-tls) thanks to symfony web server!

---

## Global features overview
<div align="center">
    <img src="https://github.com/maxence-bonnet/OCR_Bilemo/blob/master/uml/overview.png?raw=true" width="500" alt="bilemo overview">
</div>

regular User :
  - authenticate with JSON Web Token
  - refresh Token
  - read Phone item & collection, count Phones
  - read User item & collection related with his own Client
  - create, delete User related with his own Client
  - check authenticated User informations

Admin :
  - everything a user can do
  - create, read, update, delete Phone
  - create, read, update, delete Client
  - create, read, update, delete User

### More :

As an example of extension, the Brand class, related to phones, has been added and can be browsed by Users.

## Api usage

You can interact with the API either with [Postman](https://www.postman.com/) or directly on the documentation generated with [OpenApi / Swagger UI](https://api-platform.com/docs/core/openapi/)

To interact properly with the OpenApi documentation, you will need to specify the Token in the permissions (Authorizations) after authenticating yourself:
`Value: Bearer {JWT Token}`

## Api preview

You can check the demonstration [here](https://bilemo.maxence-bonnet.fr/api/docs) and interact with through Postman.

<!-- Also, you can interact with the api from the [react-admin](https://marmelab.com/react-admin/Readme.html) frontend application [here]() -->

Feel free to connect (with admin user from data fixtures) and try it, data will be reset 3 times a day.

## Code Analysis

[![SymfonyInsight](https://insight.symfony.com/projects/756e177a-adae-4cd4-8b20-035bfc02dd64/mini.svg)](https://insight.symfony.com/projects/756e177a-adae-4cd4-8b20-035bfc02dd64)
