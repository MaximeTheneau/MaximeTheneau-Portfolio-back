# Back-Portofolio-Theneau-Maxime
BackEnd Portofolio Theneau Maxime Symfony

---

[Repository Front-End](https://github.com/MaximeTheneau/MaximeTheneau-Portfolio-Next)

[Repository Back-End](https://github.com/MaximeTheneau/MaximeTheneau-Portfolio-back)

---
## Install

1. Change 'example' to your own email address (./src/Controller/Api/MessageController.php line 43).
2. Create Database : `php bin/console doctrine:database:create`
3. Configure your database connection (./.env line 27)
4. Change the value by your SMTP credentials (./.env line 44 )

```
#Install 
composer install

# Server
php -S 0.0.0.0:8000 -t public

# Utile 
php bin/console cache:clear

php bin/console lexik:jwt:generate-keypair

```
---

## Auteurs

* **Theneau Maxime** _alias_ [@MaximeTheneau](https://github.com/MaximeTheneau)
