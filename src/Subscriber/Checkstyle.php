<?php

namespace Psecio\Parse\Subscriber;

use Symfony\Component\Console\Output\OutputInterface;
use XMLWriter;
use Psecio\Parse\Event\IssueEvent;
use Psecio\Parse\Event\ErrorEvent;

/**
 * Xml generating event subscriber
 */
class Checkstyle extends Xml
{
    use OutputTrait;

    /**
     * @var XMLWriter Writer used to produce xml
     */
    private $xmlWriter;

    /**
     * Create document at scan start
     *
     * @return void
     */
    public function onScanStart()
    {
        $this->xmlWriter = new XMLWriter;
        $this->xmlWriter->openMemory();
        $this->xmlWriter->startDocument('1.0', 'UTF-8');
        $this->xmlWriter->setIndent(true);
        $this->xmlWriter->startElement('checkstyle');
        $this->xmlWriter->writeAttribute('version', '5.0');
    }

    /**
     * Output document at scan complete
     *
     * @return void
     */
    public function onScanComplete()
    {
        $this->xmlWriter->endElement();
        $this->xmlWriter->endDocument();
        $this->output->writeln(
            $this->xmlWriter->flush(),
            OutputInterface::OUTPUT_RAW
        );
    }

    /**
     * Write issue to document
     *
     * @param  IssueEvent $event
     * @return void
     */
    public function onFileIssue(IssueEvent $event)
    {
        $this->xmlWriter->startElement('file');
        $this->xmlWriter->writeAttribute('name', $event->getFile()->getPath());

        $this->xmlWriter->startElement('error');
        $this->xmlWriter->writeAttribute('line', $event->getNode()->getLine());

        $message = $event->getRule()->getDescription() . PHP_EOL;
        $message .= implode(PHP_EOL, $event->getFile()->fetchNode($event->getNode()));

        $this->xmlWriter->writeAttribute('message', $message);
        $this->xmlWriter->writeAttribute('source', $event->getRule()->getName());
        $this->xmlWriter->writeAttribute('severity', 'warning');
        $this->xmlWriter->endElement();

        $this->xmlWriter->endElement();
    }

    /**
     * Write error to document
     *
     * @param  ErrorEvent $event
     * @return void
     */
    public function onFileError(ErrorEvent $event)
    {
        $this->xmlWriter->startElement('file');
        $this->xmlWriter->writeAttribute('name', $event->getFile()->getPath());

        $this->xmlWriter->startElement('error');
        $this->xmlWriter->writeAttribute('message', $event->getMessage());
        $this->xmlWriter->writeAttribute('severity', 'error');
        $this->xmlWriter->endElement();

        $this->xmlWriter->endElement();
    }
}
