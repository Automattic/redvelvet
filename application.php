#!/usr/bin/env php
<?php
// application.php

require __DIR__.'/vendor/autoload.php';

use CupcakeLabs\RedVelvet\Tests\Command\Perf;
use Symfony\Component\Console\Application;

$application = new Application();

// ... register commands
$application->add(new Perf());

$application->run();
