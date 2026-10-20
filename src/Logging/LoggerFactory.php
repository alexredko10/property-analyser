<?php

namespace App\Logging;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

final class LoggerFactory
{

    public static function create(): LoggerInterface
    {
        $logger = new Logger('property-analyser');
        $logger->pushHandler(
            new  StreamHandler(
                dirname(__DIR__, 2) . '/data/app.log',
                Level::Info
            )
        );
        return $logger;
    }
}