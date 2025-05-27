# Inazaoui – Gestion d'albums et médias

Projet Symfony permettant le partage de photo entre utillisateurs, avec une gestion de comptes administrateurs et invités depuis un panel Admin, la création d'albums et le téléversement de médias.
Site entièrement update de la version 5 de Symfony à 7.2

---

## Pré-requis

Avant d’installer ce projet, assurez-vous d’avoir :

- PHP 8.1 ou supérieur
- [Composer](https://getcomposer.org/)
- [Symfony CLI (optionnel mais recommandé)](https://symfony.com/download)
- PostgreSQL (Docker dans notre cas)
- Node.js (>= 16.x) + npm (si vous souhaitez gérer les assets frontend)
- Un serveur web (Apache/Nginx) ou le serveur Symfony

---

## Installation

```bash
# 1. Clonez le projet
git clone https://github.com/HazzReghem/InaZaoui.git
cd inazaoui

# 2. Installez les dépendances PHP
composer install

# 3. Copiez la configuration d'environnement
cp .env .env.local

# 4. Créez la base de données
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# 5. Un fichier backup/backup.sql contient un export complet de la base PostgreSQL (structure + données).
# Si vous utilisez Docker, assurez-vous qu'il soit lancé puis éxécutez la commande suivante : 

docker exec -i inazaoui-postgres-1 psql -U postgres ina_zaoui < backup/backup.sql

# 6. Si l'import complet ne fonctionne pas, importez les tables une par une :
docker exec -i inazaoui-postgres-1 psql -U postgres ina_zaoui < backup/user.sql
docker exec -i inazaoui-postgres-1 psql -U postgres ina_zaoui < backup/album.sql
docker exec -i inazaoui-postgres-1 psql -U postgres ina_zaoui < backup/media.sql

# 6.1 Rechargez les fixtures pour corriger les différences de structure (notamment User) :

php bin/console doctrine:fixtures:load

```

---

## Lancer le serveur de développement 

symfony server:start

Accédez ensuite au projet via http://localhost:8000

---

## Tests

### Base de données de test

Configurez une base de test dans .env.test :
```bash
DATABASE_URL="postgresql://postgres:postgres@127.0.0.1:5432/ina_zaoui_test?serverVersion=16&charset=utf8"
```

### Préparation de la base de test
```bash
php bin/console doctrine:database:create --env=test
php bin/console doctrine:migrations:migrate --env=test
php bin/console doctrine:fixtures:load --env=test --no-interaction
```

### Execution des tests 
```bash
php bin/phpunit
```

### Génerer un rapport de couverture 
```bash
php bin/phpunit --coverage-text --coverage-html var/coverage-html
```

Ouvrir ensuite var/coverage-html/index.html dans votre navigateur.

--- 

## Structure du projet

```bash
├── backup/               # Données SQL 
├── config/               # Configuration de Symfony
├── migrations/           # Fichiers de migration Doctrine
├── public/               # Répertoire web root (index.php, assets compilés)
├── src/                  # Code source principal
│   ├── Controller/       # Contrôleurs HTTP (Admin et Guest)
│   ├── DataFixtures/     # Fixtures / Mise à jour des données
│   ├── Entity/           # Entités Doctrine (User, Album, Media)
│   ├── Form/             # Formulaires Symfony
│   ├── Repository/       # Requêtes personnalisées Doctrine
│   ├── EventListener/    # Listeners (ex: gestion erreurs 403)
│   └── Security/         # Logiciel lié à la sécurité (En cas de user bloqué)
├── templates/            # Fichiers Twig
├── tests/                # Tests fonctionnels et unitaires
├── translations/         # Traductions
├── uploads/              # Dossier permettant le test d'upload
├── var/                  # Logs et fichiers temporaires
└── vendor/               # Dépendances (via Composer)
```

---

## Notes sur l'implémentation

- Séparation claire entre invités et administrateurs : un rôle ROLE_ADMIN est utilisé pour définir les permissions.
- Gestion d’accès par Voter : des restrictions d’accès sont appliquées pour garantir que seuls les utilisateurs autorisés peuvent modifier leurs propres contenus.
- Tests fonctionnels assurent la cohérence de la logique métier (filtrage des utilisateurs, accès, etc.).
- Formulaires dynamiques : les champs sont affichés ou masqués selon le type d'utilisateur connecté (admin ou invité).
- Fixtures permettent de rapidement peupler l'application avec des données de démo pour les tests ou développement local.

---

## Contact

Développé par [HazzReghem](https://github.com/HazzReghem)

Projet réalisé dans le cadre de l'examen final d'une formation Symfony

---
