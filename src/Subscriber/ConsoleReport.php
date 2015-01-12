<?php

namespace Psecio\Parse\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Psecio\Parse\Event\IssueEvent;
use Psecio\Parse\Event\MessageEvent;

/**
 * Print report at scan complete
 */
class ConsoleReport implements EventSubscriberInterface
{
    use Helper\SubscriberTrait, Helper\OutputTrait;

    /**
     * @var integer Number of scanned files
     */
    private $fileCount;

    /**
     * @var array List of test failures
     */
    private $issues;

    /**
     * @var array List of scan errors
     */
    private $errors;

    /**
     * Reset values on scan start
     *
     * @return null
     */
    public function onScanStart()
    {
        $this->fileCount = 0;
        $this->issues = [];
        $this->errors = [];
    }

    /**
     * Write report on scan complete
     *
     * @return null
     */
    public function onScanComplete()
    {
        $this->output->writeln($this->getReport());
    }

    /**
     * Increment files scanned counter
     *
     * @return null
     */
    public function onFileOpen()
    {
        $this->fileCount++;
    }

    /**
     * Save issue event
     *
     * @param  IssueEvent $event
     * @return null
     */
    public function onFileIssue(IssueEvent $event)
    {
        $this->issues[] = $event;
    }

    /**
     * Save error event
     *
     * @param  MessageEvent $event
     * @return null
     */
    public function onFileError(MessageEvent $event)
    {
        $this->errors[] = $event;
    }

    /**
     * Format using different formats pending on $number
     *
     * @param  string $singular Format used in singularis
     * @param  string $plural   Format used in pluralis
     * @param  int|float $count Format argument and numerus marker
     * @return string
     */
    private function pluralize($singular, $plural, $count)
    {
        return $count == 1 ? sprintf($singular, $count) : sprintf($plural, $count);
    }

    /**
     * Get report
     *
     * @return string
     */
    private function getReport()
    {
        return "\n\n" . ($this->errors || $this->issues ? $this->getFailureReport() : $this->getPassReport());
    }

    /**
     * Get report for all tests pass
     *
     * @return string
     */
    private function getPassReport()
    {
        return $this->pluralize(
            "<info>OK (%d file scanned)</info>",
            "<info>OK (%d files scanned)</info>",
            $this->fileCount
        );
    }

    /**
     * Get failure report
     *
     * @return string
     */
    private function getFailureReport()
    {
        return $this->getErrorReport()
            . $this->getIssueReport()
            . sprintf(
                "<error>FAILURES!</error>\n<error>Scanned: %d, Errors: %d, Issues: %d.</error>",
                $this->fileCount,
                count($this->errors),
                count($this->issues)
            );
    }

    /**
     * Get issue report
     *
     * @return string
     */
    private function getIssueReport()
    {
        $str = '';

        if ($this->issues) {
            $str .= $this->pluralize(
                "There was %d issue\n\n",
                "There were %d issues\n\n",
                count($this->issues)
            );
        }

        foreach ($this->issues as $index => $issueEvent) {
            $attrs = $issueEvent->getNode()->getAttributes();
            $str .= sprintf(
                "<comment>%d) %s on line %d</comment>\n%s\n<error>> %s</error>\n\n",
                $index + 1,
                $issueEvent->getFile()->getPath(),
                $attrs['startLine'],
                $issueEvent->getTest()->getDescription(),
                trim(implode("\n> ", $issueEvent->getFile()->getLines($attrs['startLine'])))
            );
        }

        return $str;
    }

    /**
     * Get error report
     *
     * @return string
     */
    private function getErrorReport()
    {
        $str = '';

        if ($this->errors) {
            $str .= $this->pluralize(
                "There was %d error\n\n",
                "There were %d errors\n\n",
                count($this->errors)
            );
        }

        foreach ($this->errors as $index => $errorEvent) {
            $str .= sprintf(
                "<comment>%d) %s</comment>\n<error>%s</error>\n\n",
                $index + 1,
                $errorEvent->getFile()->getPath(),
                $errorEvent->getMessage()
            );
        }

        return $str;
    }
}
