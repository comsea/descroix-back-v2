# descroix-backend

Backend API + XML sync pour le site Cabinet Descroix. Déployé actuellement sur un VPS personnel de secours via Dokploy (Docker), en attendant une vraie infra.

## Structure

- `api/` — endpoints PHP (index, getNew, getOne, search, contact). Lit les annonces depuis `/var/www/data/dataset.xml` en lecture locale (pas de fetch réseau).
- `sync/index.php` — script qui va chercher le flux XML source (`clients.ac3-distribution.com`, plateforme immo-facile) et réécrit `/var/www/data/dataset.xml`.
- `sync/crontab` + `docker-entrypoint.sh` — le refresh tourne **dans le conteneur**, toutes les 15 min, via cron. Un premier refresh se fait aussi au démarrage du conteneur pour ne pas partir avec un `dataset.xml` vide juste après un déploiement.

Tout est packagé dans un seul `Dockerfile` à la racine — `docker build`/déploiement suffit, aucune tâche cron à configurer à la main sur l'hôte.

## Déploiement

1. Build l'image : `docker build -t descroixapi .`
2. Run : `docker run -d -p 80:80 descroixapi`
   (un volume nommé sur `/var/www/data` est recommandé pour garder les données entre deux redéploiements, mais pas obligatoire — le script de sync tourne au démarrage du conteneur de toute façon)
3. Reverse proxy (Traefik/nginx) devant le conteneur pour le TLS + routage du domaine `descroixapi.comsea.fr`.

## Historique

Anciennement deux hébergements OVH séparés (`descroixapi.comsea.fr` et `descroixxml.comsea.fr` communiquant en HTTPS). Fusionnés ici sur un seul host pour supprimer la dépendance réseau entre les deux (source de hangs/timeouts en prod).
