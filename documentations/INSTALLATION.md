# 📦 Guide d'installation - Projet Symfony + Vue.js

## Prérequis

- Docker et Docker Compose installés
- Git

## 🚀 Installation

### 1. Cloner le projet

```bash
git clone [url-du-repo]
cd my_project
```

### 2. Configuration de l'environnement

Copier le fichier d'exemple et configurer vos variables :

```bash
cp .env.local.example .env.local
```

Éditer `.env.local` et modifier :
- `N8N_API_KEY` : Générer une clé API sécurisée (64 caractères)
- `API_ADMIN_USER_EMAIL` : Email de l'utilisateur admin

### 3. Démarrer les conteneurs Docker

```bash
docker-compose up -d
```

Cela va démarrer :
- **PHP** : Backend Symfony
- **Nginx** : Serveur web (port 8080)
- **MySQL** : Base de données (port 3307)
- **PHPMyAdmin** : Interface web pour MySQL (port 8081)
- **Front** : Serveur de développement Vite.js (port 5173)

### 4. Installer les dépendances PHP

```bash
docker-compose exec php composer install
```

### 5. Installer les dépendances Node.js

Pour le backend (Webpack Encore) :
```bash
docker-compose exec node npm install
```

Pour le frontend Vue.js :
```bash
cd front
npm install
cd ..
```

### 6. Créer la base de données

```bash
# Créer la base de données
docker-compose exec php php bin/console doctrine:database:create

# Exécuter les migrations
docker-compose exec php php bin/console doctrine:migrations:migrate
```

### 7. (Optionnel) Charger les données de test

Si vous avez des fixtures ou données de test :

```bash
docker-compose exec php php bin/console doctrine:fixtures:load
```

## 🌐 Accès aux services

Une fois tous les conteneurs démarrés :

- **Application Symfony** : http://localhost:8080
- **Frontend Vue.js (dev)** : http://localhost:5173
- **PHPMyAdmin** : http://localhost:8081
  - Serveur : `db`
  - Utilisateur : `root`
  - Mot de passe : `root`
  - Base de données : `courTotal`

## 🛠️ Commandes utiles

### Docker

```bash
# Démarrer les conteneurs
docker-compose up -d

# Arrêter les conteneurs
docker-compose down

# Voir les logs
docker-compose logs -f

# Voir les logs d'un service spécifique
docker-compose logs -f php
docker-compose logs -f nginx

# Rebuilder les conteneurs
docker-compose up -d --build
```

### Symfony

```bash
# Exécuter une commande Symfony
docker-compose exec php php bin/console [commande]

# Créer une nouvelle migration
docker-compose exec php php bin/console make:migration

# Vider le cache
docker-compose exec php php bin/console cache:clear
```

### Frontend

```bash
# Mode développement (avec hot reload)
cd front
npm run dev

# Build pour production
npm run build
```

### Base de données

```bash
# Accéder à MySQL via CLI
docker-compose exec db mysql -u root -p
# Mot de passe : root

# Créer un backup
docker-compose exec db mysqldump -u root -proot courTotal > backup.sql

# Restaurer un backup
docker-compose exec -T db mysql -u root -proot courTotal < backup.sql
```

## 🐛 Résolution de problèmes

### Les conteneurs ne démarrent pas

```bash
# Vérifier les logs
docker-compose logs

# Rebuilder les images
docker-compose down
docker-compose up -d --build
```

### Erreur de permissions

```bash
# Donner les permissions sur le dossier var/
sudo chmod -R 777 var/
```

### Le frontend ne se connecte pas au backend

Vérifier que tous les services sont sur le même réseau Docker `connectPerso` et que les URLs sont correctes dans la configuration Vite.

### Base de données inaccessible

```bash
# Vérifier que le conteneur db est bien démarré
docker-compose ps

# Vérifier les logs de MySQL
docker-compose logs db
```

## 📝 Notes

- Le dossier `front/` contient l'application Vue.js
- Les assets Webpack sont dans `assets/`
- Les migrations sont dans `migrations/`
- La configuration Docker est dans `compose.yaml`
