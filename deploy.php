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
    ->set('http_user', 'www-data')
    ->set('identity_file', '~/.ssh/id_rsa');

// Tasks
task('frontend:build', function () {
    runLocally('docker-compose run --rm front-build');
});

task('cache:clear', function () {
    run('{{bin/console}} cache:clear --env=prod --no-debug');
});

// Hooks
after('deploy:vendors', 'frontend:build');
after('deploy:cache:clear', 'cache:clear');
after('deploy:failed', 'deploy:unlock');
