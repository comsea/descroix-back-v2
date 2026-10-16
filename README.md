# descroix-backend

Backend API + XML sync pour le site Cabinet Descroix. Destiné à un hébergement mutualisé OVH (Performance).

## Structure

- `public/` — document root à déclarer dans le Multisite OVH. Contient les endpoints PHP (`index`, `getNew`, `getOne`, `search`, `contact`). Lisent `data/dataset.xml` en local (pas de fetch réseau par requête).
- `src/Mailer.php` — envoi des emails du formulaire de contact via le relais SMTP Brevo (PHPMailer).
- `cron/sync.php` — va chercher le flux XML source (`clients.ac3-distribution.com`, plateforme immo-facile) et réécrit `data/dataset.xml`. À exécuter toutes les 15 min via les *Tâches planifiées* du manager OVH : `php /chemin/vers/cron/sync.php`.
- `data/` — hors document root, non accessible publiquement. Contient `dataset.xml` (généré par le sync) et `sync.log`.
- `bootstrap.php` — charge l'autoload Composer et le `.env`, défini les constantes `DATA_PATH` / `LOG_PATH`. Requis par chaque script public et par `cron/sync.php`.

## Configuration

Copier `.env.example` en `.env` et renseigner les valeurs réelles (jamais commité) :
- Identifiants SMTP Brevo (`BREVO_SMTP_*`) et adresses d'envoi/réception (`MAIL_*`)
- URL du flux XML source (`XML_SOURCE_URL`)

## Déploiement sur OVH mutualisé (Performance)

1. `composer install --no-dev --optimize-autoloader` (Composer disponible en SSH sur l'offre Performance)
2. Déclarer `public/` comme document root pour `api.descroiximmobilier.com` dans le Multisite
3. Créer le `.env` directement sur le serveur à partir de `.env.example`
4. Créer une tâche planifiée OVH : `php /chemin/vers/cron/sync.php`, toutes les 15 min
5. Lancer `cron/sync.php` une fois manuellement après le premier déploiement pour générer `data/dataset.xml` (sinon les endpoints renvoient 502 jusqu'au premier passage du cron)

## Développement local (Docker, optionnel)

```sh
docker build -t descroixapi .
docker run -d -p 8080:80 -v $(pwd)/.env:/var/www/.env descroixapi
```

Le `.env` est monté en volume plutôt que copié dans l'image (il ne doit jamais être commité ni baké dans une image).

## Historique

Anciennement deux hébergements OVH séparés (`descroixapi.comsea.fr` et `descroixxml.comsea.fr` communiquant en HTTPS), puis fusionnés et déployés via Dokploy (Docker) sur un VPS personnel de secours. Migré ici vers un hébergement mutualisé OVH Performance, sous le nouveau domaine `api.descroiximmobilier.com`.
