<?php
declare(strict_types=1);

use Ekvio\Integration\Contracts\Profiler;
use Ekvio\Integration\Invoker\CsvConverter;
use Ekvio\Integration\Invoker\TypicalUserRenameReport;
use Ekvio\Integration\Invoker\UserRenameReport;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

require_once __DIR__ . '/../vendor/autoload.php';

class Dumper implements Profiler
{
    public function profile(string $message): void
    {
        fwrite(STDOUT, $message . PHP_EOL);
    }
}


(new TypicalUserRenameReport(
    new Filesystem(new Local(__DIR__ . '/tmp')),
    new UserRenameReport(),
    new CsvConverter(),
    new Dumper()
))(
    [
        'prev' => [
            ['from' => 'x999x', 'to' => 'petrov'],
            ['from' => 'y111y', 'to' => 'sidorov'],
            ['from' => 'z222z', 'to' => 'ivanov']
        ],
        'reportFilename' => 'rename.csv'
    ]
);