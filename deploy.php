<?php

namespace Deployer;

require 'recipe/symfony.php';

// Config
set('repository', getenv('DEPLOY_REPOSITORY') ?: 'git@github.com:cundovar/vue-symfony.git');
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
    ->set('hostname', getenv('DEPLOY_HOST') ?: '51.83.32.36')
    ->set('remote_user', getenv('DEPLOY_USER') ?: 'deployer')
    ->set('deploy_path', getenv('DEPLOY_PATH') ?: '/var/www/coursPoleS')
    ->set('branch', getenv('DEPLOY_BRANCH') ?: 'main')
    ->set('http_user', 'www-data')
    ->set('forward_agent', true);

// Tasks
task('easyadmin:build', function () {
    run(
        'docker run --rm --user "$(id -u):$(id -g)" --env HOME=/tmp '
        . '--volume {{release_path}}:/app --workdir /app node:22 '
        . 'sh -c "npm ci && npm run build"'
    );
});

task('frontend:build', function () {
    run(
        'cd {{release_path}} && HOST_UID=$(id -u) HOST_GID=$(id -g) '
        . 'docker compose run --rm front-build'
    );
});

task('assets:build', [
    'easyadmin:build',
    'frontend:build',
]);

task('cache:clear', function () {
    run('{{bin/console}} cache:clear --env=prod --no-debug');
});

// Hooks
after('deploy:vendors', 'assets:build');
after('deploy:cache:clear', 'cache:clear');
after('deploy:failed', 'deploy:unlock');
