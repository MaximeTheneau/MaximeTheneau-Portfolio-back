# Routes

## Connection
---

| URL | Méthode HTTP | Contrôleur       | Méthode | Titre HTML           | Commentaire    |
| --- | ------------ | ---------------- | ------- | -------------------- | -------------- |
| `/login` | `GET`        | `SecurityController` | `app_login`  | Please sign in | Page de connection|
| `/logout` | `GET`        | `SecurityController` | `app_logout`  | Disconnection | Page de déconnexion |

## Back-Office
---
| URL | Méthode HTTP | Contrôleur       | Méthode | Titre HTML           | Commentaire    |
| --- | ------------ | ---------------- | ------- | -------------------- | -------------- |
|   Users |
| `/back/user` | `GET` | `UserController` | `app_back_user_index`  | Users List | Tous les utilisateurs |
| `/back/user/{id}`   | `GET` | `UserController` | `app_back_user_show`  | Edit User | Page détail d'un utilisateur |
| `/back/user`    | `POST` | `UserController` | `app_back_user_add`| Create new User | Ajoute un nouveau utilisateur dans la liste |
| `/back/user/{id}`   | `PATCH`| `UserController`| `app_back_user_edit`  | User |Modifie les détails de l'utilisateur |
| `/back/user`    | `DELETE`| `UserController` | `app_back_user_delete`| | Supprime un utilisateur de la liste | 
|   Categories |
| `/back/categories` | `GET` | `CategoriesController` | `app_back_categories_index`  | Categories List | Tous les categories |
| `/back/categories/{id}`   | `GET` | `CategoriesController` | `app_back_categories_show`  | Categories | Page détail d'une categorie  |
| `/back/categories`    | `POST` | `CategoriesController` | `app_back_categories_add`| Create new Categories | Ajoute une nouvelle categorie dans la liste |
| `/back/categories/{id}`   | `PATCH`| `CategoriesController`| `app_back_categories__show`  | Edit Category |Modifie les détails de la categorie |
| `/back/categories`    | `DELETE`| `CategoriesController` | `app_back_categories_delete`| | Supprime une categorie de la liste | 
|   Experiences |
| `/back/experiences` | `GET` | `ExperiencesController` | `app_back_experiences_index`  | Experiences List | Tous les expériences |
| `/back/experiences/{id}`   | `GET` | `ExperiencesController` | `app_back_experiences_show`  | Experiences | Page détail d'une expérience  |
| `/back/experiences`    | `POST` | `ExperiencesController` | `app_back_experiences_add`| Create new Experiences | Ajoute une nouvelle expérience dans la liste |
| `/back/experiences/{id}`   | `PATCH`| `ExperiencesController`| `app_back_experiences__show`  | Edit Experiences |Modifie les détails de l'expérience |
| `/back/experiences`    | `DELETE`| `ExperiencesController` | `app_back_experiences_delete`| | Supprime une expérience de la liste | 



## Api
---
| URL | Méthode HTTP | Contrôleur       | Méthode | Titre HTML           | Commentaire    |
| --- | ------------ | ---------------- | ------- | -------------------- | -------------- |
| `/back/user` | `GET` | `UserController` | `app_back_user_index`  | Users List | Tous les utilisateurs |
| `/back/user/{id}`   | `GET` | `UserController` | `app_back_user_show`  | Edit User | Page détail d'un utilisateur |