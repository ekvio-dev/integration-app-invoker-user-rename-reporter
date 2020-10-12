<?php
declare(strict_types=1);

namespace Ekvio\Integration\Invoker;

use Ekvio\Integration\Contracts\Report\Collector;
use Ekvio\Integration\Contracts\Report\Converter;
use League\Csv\Writer;

/**
 * Class CsvConverter
 * @package Ekvio\Integration\Invoker
 */
class CsvConverter implements Converter
{
    private const DELIMITER = ';';
    /**
     * @param Collector $collector
     * @param array $options
     * @return mixed|void
     */
    public function convert(Collector $collector, array $options = [])
    {
        $writer = self::createWriter($options);
        $writer->insertOne($collector->header());
        $writer->insertAll($collector->content());

        return $writer->getContent();
    }

    /**
     * @param array $options
     * @return Writer
     * @throws \League\Csv\Exception
     */
    private static function createWriter(array $options = []): Writer
    {
        $writer = Writer::createFromString();
        $writer->setDelimiter($options['delimiter'] ?? self::DELIMITER);

        return $writer;
    }
}