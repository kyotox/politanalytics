<?php

namespace App\MessageHandler;

use App\Message\PersonContactTask;
use App\Service\EPContactImporter;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class PersonContactTaskHandler
{
    private EPContactImporter $EPContactImporter;

    public function __construct(EPContactImporter $importer)
    {
        $this->EPContactImporter = $importer;
    }

    public function __invoke(PersonContactTask $message)
    {
        $contactData = $this->EPContactImporter->importContactData($message->getExternalId());

    }
}