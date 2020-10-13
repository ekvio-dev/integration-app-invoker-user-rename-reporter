<?php
declare(strict_types=1);

namespace Ekvio\Integration\Invoker;

use Ekvio\Integration\Contracts\Invoker;
use Ekvio\Integration\Contracts\Profiler;
use Ekvio\Integration\Contracts\Report\Converter;
use Ekvio\Integration\Contracts\Report\Reporter;
use League\Csv\Exception;
use League\Csv\Reader;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
use RuntimeException;

/**
 * Class TypicalUserRenameReport
 * @package Ekvio\Integration\Invoker
 */
class TypicalUserRenameReport implements Invoker
{
    private const NAME = 'Rename reporter';
    /**
     * @var FilesystemInterface
     */
    private $filesystem;
    /**
     * @var Reporter
     */
    private $reporter;
    /**
     * @var Converter
     */
    private $converter;
    /**
     * @var Profiler
     */
    private $profiler;
    /**
     * @var bool
     */
    private $append = true;

    /**
     * TypicalUserRenameReport constructor.
     * @param FilesystemInterface $filesystem
     * @param Reporter $report
     * @param Converter $converter
     * @param Profiler $profiler
     * @param array $config
     */
    public function __construct(
        FilesystemInterface $filesystem,
        Reporter $report,
        Converter $converter,
        Profiler $profiler,
        array $config = []
    )
    {
        $this->filesystem = $filesystem;
        $this->reporter = $report;
        $this->converter = $converter;
        $this->profiler = $profiler;

        if(isset($config['append']) && is_bool($config['append'])) {
            $this->append = $config['append'];
        }
    }

    /**
     * @param array $arguments
     */
    public function __invoke(array $arguments = [])
    {
        if(!isset($arguments['prev'])) {
            throw new RuntimeException('No rename log in "prev" key');
        }

        if(!$arguments['prev']) {
            $this->profiler->profile('No renamed users for report...');
            return;
        }

        if(empty($arguments['parameters']['reportFilename'])) {
            throw new RuntimeException('Report file name not set');
        }

        $filename = $arguments['parameters']['reportFilename'];
        $records = [];
        if($this->append) {
            $this->profiler->profile(sprintf('Get records from %s report for append mode', $filename));
            $records = $this->getExistReportRecords($filename);
        }

        $this->profiler->profile('Building rename report...');
        $collectedData = $this->reporter->build($arguments['prev'], ['records' => $records]);

        $this->profiler->profile('Converting report data...');
        $data = $this->converter->convert($collectedData);

        if ($this->filesystem->put($filename, $data) === false) {
            throw new RuntimeException(sprintf('Failed to write from %s file...', $filename));
        }

    }

    /**
     * @param string $filename
     * @return array
     * @throws Exception
     * @throws FileNotFoundException
     */
    private function getExistReportRecords(string $filename): array
    {
        $data = [];
        $this->profiler->profile(sprintf('Check %s file existence...', $filename));
        if ($this->filesystem->has($filename)) {
            if (!$content = $this->filesystem->read($filename)) {
                throw new RuntimeException(sprintf('Failed to read from %s file...', $filename));
            }

            $csv = Reader::createFromString($content);
            $csv->setHeaderOffset(0);
            $csv->setDelimiter(';');

            return iterator_to_array($csv->getRecords());
        }

        return $data;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return self::NAME;
    }
}