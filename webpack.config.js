const Encore = require('@symfony/webpack-encore');

// Configuration uniquement pour EasyAdmin
// Ne pas interférer avec Vite qui gère le front-end Vue.js

// Répertoire où les assets compilés seront stockés
Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    
    // EasyAdmin nécessite ces assets de base
    .addEntry('easyadmin', './assets/easyadmin.js')
    
    // Désactiver le hot module replacement pour éviter les conflits avec Vite
    .disableSingleRuntimeChunk()
    
    // Production vs development
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    
    // CSS/Sass processing
    .enableSassLoader()
    
    // Seulement les polyfills nécessaires pour EasyAdmin
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'entry';
        config.corejs = 3;
    });

// Configuration séparée pour éviter les conflits avec Vite
Encore.configureWatchOptions(function(watchOptions) {
    // Ignorer les fichiers front/ pour éviter les conflits avec Vite
    watchOptions.ignored = /front/;
});

module.exports = Encore.getWebpackConfig();