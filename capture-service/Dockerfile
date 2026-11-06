FROM mcr.microsoft.com/playwright:v1.57.0-noble

WORKDIR /app

COPY capture/package.json capture/package-lock.json ./capture/
RUN npm ci --prefix capture --omit=dev

COPY capture/src ./capture/src
COPY capture-service/src ./capture-service/src

RUN chown -R pwuser:pwuser /app

USER pwuser

ENV PLAYWRIGHT_BROWSERS_PATH=/ms-playwright \
    NODE_ENV=production

EXPOSE 3000

CMD ["node", "capture-service/src/server.js"]
