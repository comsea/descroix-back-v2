#!/bin/sh

# Le sync XML est planifié en externe (Tâches planifiées OVH en prod), pas dans ce conteneur
exec "$@"
