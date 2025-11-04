<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        // __DIR__ . '/bootstrap',
        // __DIR__ . '/config',
        // __DIR__ . '/lang',
        // __DIR__ . '/public',
        // __DIR__ . '/resources',
        // __DIR__ . '/routes',
        // __DIR__ . '/tests',
    ])
    ->withPreparedSets(
        deadCode: false, // enable this...
        codeQuality: true,
        typeDeclarations: true,
        privatization: true,
        earlyReturn: true,
        strictBooleans: true,
    )
    // uncomment to reach your current PHP version
    ->withPhpSets();
