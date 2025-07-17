<?php
namespace Deployer;

require 'recipe/symfony.php';

// Config
set('repository', 'git@github.com:cundovar/vue-symfony.git');
set('git_tty', true);

// Shared files and directories
add('shared_files', [
    '.env.local',
    '.env.prod'
]);

add('shared_dirs', [
    'var/log',
    'var/cache',
    'var/sessions',
    'public/uploads',
    'mysql_data'
]);

add('writable_dirs', [
    'var',
    'var/cache',
    'var/log',
    'var/sessions',
    'public/uploads'
]);

// Hosts
host('production')
    ->set('hostname', '51.83.32.36')
    ->set('remote_user', 'deployer')
    ->set('deploy_path', '/var/www/coursPoleS')
    ->set('branch', 'main')
    ->set('http_user', 'www-data');

// Tasks
task('frontend:build', function () {
    // Option 1: Build local avec Docker
    runLocally('docker-compose run --rm front-build');
    
    // Option 2: Build sur le serveur (alternative)
    // cd('{{release_path}}/front');
    // run('npm install');
    // run('npm run build');
});

task('database:migrate', function () {
    run('{{bin/console}} doctrine:migrations:migrate --no-interaction');
});

task('cache:clear', function () {
    run('{{bin/console}} cache:clear --env=prod --no-debug');
});

// Hooks
after('deploy:vendors', 'frontend:build');
after('deploy:cache:clear', 'database:migrate');
after('database:migrate', 'cache:clear');
after('deploy:failed', 'deploy:unlock');
