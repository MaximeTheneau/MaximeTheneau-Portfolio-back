# Dictionnaire de données

##  Utilisateurs (`users`)

|Champ|Type|Spécificités|Description|
|-|-|-|-|
|id|INT|PRIMARY KEY, NOT NULL, UNSIGNED, AUTO_INCREMENT|L'identifiant de l'utilisateur|
|name|VARCHAR(128)|NOT NULL|Le nom de l'utilisateur|
|email|VARCHAR(128)|NOT NULL|L'email de l'utilisateur|
|password|VARCHAR(128)|NOT NULL|Le mot de passe de l'utilisateur|
|created_at|TIMESTAMP|NOT NULL, DEFAULT CURRENT_TIMESTAMP|La date de création de l'utilisateur|
|updated_at|TIMESTAMP|NULL|La date de la dernière mise à jour de l'utilisateur|

## Expériences (`experience`)

|Champ|Type|Spécificités|Description|
|-|-|-|-|
|id|INT|PRIMARY KEY, NOT NULL, UNSIGNED, AUTO_INCREMENT|L'identifiant de notre expérience|
|title|VARCHAR(255)|NOT NULL|Le titre de l'expérience|
|imageSvg|VARCHAR(255)|NULL|Le lien de l'image au format Svg de l'expérience|
|imageWebp|VARCHAR(255)|NULL|Le lien de l'image au format Webp de l'expérience|
|contents|VARCHAR(1024)|NOT NULL|Contenu de l'expérience|
|contents2|VARCHAR(1024)|NULL|2em Contenu de l'expérience|
|contents3|VARCHAR(1024)|NULL|3em Contenu de l'expérience|
|created_at|TIMESTAMP|NOT NULL, DEFAULT CURRENT_TIMESTAMP|La date de création de l'expérience|
|updated_at|TIMESTAMP|NULL|La date de la dernière mise à jour de l'expérience|
|category_id|TIMESTAMP|NULL|Identifiant de la catégorie de l'expérience|


## Catégories (`categories`)

|Champ|Type|Spécificités|Description|
|-|-|-|-|
|id|INT|PRIMARY KEY, NOT NULL, UNSIGNED, AUTO_INCREMENT|L'identifiant de notre catégorie|
|title|VARCHAR(128)|NOT NULL|Le nom de la catégorie|
|idTitle|VARCHAR(128)|NOT NULL|L'identifiant de la catégorie|
|imageWebp|VARCHAR(255)|NOT NULL|Le liens de l'image de la catégorie|
|imageSvg|VARCHAR(255)|NOT NULL|Le liens de l'image de la catégorie|
|created_at|TIMESTAMP|NOT NULL, DEFAULT CURRENT_TIMESTAMP|La date de création de la catégorie|
|updated_at|TIMESTAMP|NULL|La date de la dernière mise à jour de la catégorie|

