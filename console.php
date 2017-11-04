#!/usr/bin/env php
<?php

set_time_limit(600);

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Gammabeam82\Resizer\ResizeCommand;

$application = new Application();
$application->add(new ResizeCommand());
$application->run();