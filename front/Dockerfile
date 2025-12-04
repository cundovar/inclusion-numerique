FROM node:20

# Dossier de travail
WORKDIR /app

# Copier les dépendances
COPY package*.json ./
RUN npm install

# Copier le reste
COPY . .

# Exposer le port de Vite
EXPOSE 5173

# Lancer le dev server (avec host 0.0.0.0 pour Docker)
CMD ["npm", "run", "dev:dock"]
