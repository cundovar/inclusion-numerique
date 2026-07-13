#!/bin/bash

echo "Arret des conteneurs front et storybook..."
docker compose stop front storybook

echo "Build en cours..."
HOST_UID="$(id -u)" HOST_GID="$(id -g)" docker compose run --rm front-build

echo "Redemarrage de nginx..."
docker compose up -d nginx

echo "Build termine !"
