<?php
declare(strict_types=1);

namespace Ekvio\Integration\Invoker;

use DateTimeImmutable;
use Ekvio\Integration\Contracts\Report\Collector;
use Ekvio\Integration\Contracts\Report\Reporter;

/**
 * Class UserRenameReport
 * @package Ekvio\Integration\Invoker
 */
class UserRenameReport implements Reporter
{
    /**
     * @var array|string[]
     */
    private $header = ['Дата добавления', 'Старый логин', 'Новый логин'];
    /**
     * @var string
     */
    private $dateTimeFormat = 'Y-m-d H:i:s';

    /**
     * UserRenameReport constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        if(isset($config['header']) && is_array($config['header'])) {
            $this->header = $config['header'];
        }

        if(isset($config['dateTimeFormat'])) {
            $this->dateTimeFormat = $config['dateTimeFormat'];
        }
    }
    /**
     * @param $data
     * @param array $options
     * @return Collector
     */
    public function build($data, array $options = []): Collector
    {
        $currentDT = (new DateTimeImmutable())->format($this->dateTimeFormat);
        $records = [];
        if(isset($options['records']) && is_array($options['records'])) {
            $records = $options['records'];
        }

        foreach ($data as $row) {
            $records[] = [$currentDT, $row['from'], $row['to']];
        }

        return ReportDataCollector::create($this->header, $records);
    }
}