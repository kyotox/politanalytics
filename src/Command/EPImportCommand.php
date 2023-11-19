<?php

namespace App\Command;

use App\Entity\Person;
use App\Message\PersonContactTask;
use App\Service\EPContactImporter;
use App\Service\EPImporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:import-ep',
    description: 'Import members of the European Parliament.',
    hidden: false
)]
class EPImportCommand extends Command
{
    private EPImporter $apiImporter;
    private MessageBusInterface $bus;

    public function __construct(EPImporter $EPImporter, MessageBusInterface $bus)
    {
        parent::__construct();

        $this->bus = $bus;
        $this->apiImporter = $EPImporter;
    }
    protected function configure(): void
    {
        $this->addOption('contact_import', 'null', InputOption::VALUE_OPTIONAL, 'Import contact data? yes/no/queue', 'yes');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $count = $this->apiImporter->getTotalCount();
        if(!$count){
            $output->writeln('No data found in current dataset!');
            return Command::FAILURE;
        } else {
            $output->writeln("Found $count entries to be processed");
        }

        for ($currentIndex = 0; $currentIndex <= $count; $currentIndex++) {
            try {
                $output->writeln("Importing index $currentIndex");
                $person = $this->apiImporter->importOne();
                if($person instanceof Person) {
                    $output->writeln(sprintf("Sucessfully imported %s with id %s", $person->getName(), $person->getExternalId()));
                } else {
                    break;
                }

                if ($input->getOption('contact_import') == 'yes'){
                    $epContactImporter = new EPContactImporter();
                    $contactData = $epContactImporter->importContactData($person->getExternalId());
                    foreach ($contactData as $type => $value){
                        $output->writeln("Imported $type: $value");
                    }
                } else if($input->getOption('contact_import') == 'queue') {
                    $this->bus->dispatch(new PersonContactTask($person->getExternalId()));
                }

            } catch (\Throwable $e) {
                $output->writeln("Node with index $currentIndex could not be processed");
                $output->writeln($e->getMessage());
            }
        }

        return Command::SUCCESS;
    }
}