#!/usr/bin/env php
<?php
namespace SmkSoftware\ScrapeIt;

require __DIR__ . '/vendor/autoload.php';

use SmkSoftware\ScrapeIt\Console\RunCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->setName('ScrapeIt');
$application->add(new RunCommand());
$application->run();
