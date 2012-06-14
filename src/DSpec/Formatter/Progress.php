<?php

namespace DSpec\Formatter;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Console\Output\OutputInterface;
use DSpec\Event\ExampleFailEvent;
use DSpec\Event\ExamplePassEvent;
use DSpec\Event\ExamplePendEvent;
use DSpec\Event\ExampleSkipEvent;
use DSpec\Events;

class Progress implements EventSubscriberInterface
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var int
     */
    protected $counter = 0;

    /**
     * @var float
     */
    protected $startTime = 0;

    /**
     * Constructor
     *
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    static public function getSubscribedEvents()
    {
        return array(
            Events::EXAMPLE_FAIL => array('onExampleFail', 0),
            Events::EXAMPLE_PASS => array('onExamplePass', 0),
            Events::EXAMPLE_PEND => array('onExamplePend', 0),
            Events::EXAMPLE_SKIP => array('onExampleSkip', 0),
            Events::SUITE_END    => array('onSuiteEnd', 0),
            Events::COMPILER_START => array('onCompilerStart', 0),
        );
    }

    /**
     * @param ExampleFailEvent $e
     */
    public function onExampleFail(ExampleFailEvent $e)
    {
        $this->writeProgress("<error>F</error>");
    }


    /**
     * @param ExamplePassEvent $e
     */
    public function onExamplePass(ExamplePassEvent $e)
    {
        $this->writeProgress('<info>.</info>');
    }

    /**
     * @param ExamplePendEvent $e
     */
    public function onExamplePend(ExamplePendEvent $e)
    {
        $this->writeProgress('<comment>P</comment>');
    }

    /**
     * @param ExampleSkipEvent $e
     */
    public function onExampleSkip(ExampleSkipEvent $e)
    {
        $this->writeProgress('<comment>S</comment>');
    }

    /**
     * @param Event
     */
    public function onCompilerStart(Event $e)
    {
        $this->startTime = microtime(true);
    }

    /**
     * @param Event
     */
    public function onSuiteEnd(Event $e)
    {
        $duration = microtime(true) - $this->startTime;
        $this->output->writeln("");
        $this->output->writeln("");
        $this->output->writeln(sprintf("Finished in %s seconds", round($duration, 5)));

        $failures = $e->getReporter()->getFailures();
        $total = $e->getExampleGroup()->total();
        $format = count($failures) > 0 ? 'error' : 'info';
        $r = $e->getReporter();

        $resultLine = sprintf(
            "<%s>%d example%s, %d failure%s", 
            $format, 
            $total, 
            $total != 1 ? 's' : '', 
            count($failures), 
            count($failures) != 1 ? 's' : ''
        );

        if (count($r->getPending())) {
            $resultLine.= sprintf(", %d pending", count($r->getPending()));
        }

        if (count($r->getSkipped())) {
            $resultLine.= sprintf(", %d skipped", count($r->getSkipped()));
        }

        $resultLine.= "</$format>";

        $this->output->writeln($resultLine);

        $groups = array();
        foreach($failures as $f) {
            $indent = 0;
            foreach($f->getAncestors() as $eg) {
                $this->output->writeln("<comment>" . str_repeat(" ", $indent) . $eg->getTitle() . "</comment>");
                $indent+=2;
            }
            $this->output->writeln("<comment>" . str_repeat(" ", $indent) . ($f->getFailureException()->getMessage() ?: "{no message}") . "</comment>");
        }
    }

    /**
     * Write the progress dots and Fs
     *
     * @param string $string
     */
    public function writeProgress($string)
    {
        $this->output->write($string, ++$this->counter % 80 == 0);
    }

}
