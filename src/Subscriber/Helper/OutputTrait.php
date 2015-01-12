<?php

namespace Psecio\Parse\Subscriber\Helper;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Helper to simplify writing to the console
 */
trait OutputTrait
{
    /**
     * @var OutputInterface Registered output
     */
    protected $output;

    /**
     * Register output interface
     *
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Write to console
     *
     * @param  string $format sprintf format string
     * @param  mixed  ...$arg Any number of sprintf arguments
     * @return null
     */
    protected function write()
    {
        $this->output->write(call_user_func_array('sprintf', func_get_args()));
    }
}
