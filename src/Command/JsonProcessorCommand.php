<?php

namespace App\Command;

use App\Service\JsonProcessorService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:json-processor',
    description: 'Process request.json file and seed the database with initial data'
)]
class JsonProcessorCommand extends Command
{
    public function __construct(
        private JsonProcessorService $processorService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Processing request.json file');

        try {
            $result = $this->processorService->processFile();

            $io->success([
                "Successfully processed request.json",
                "Fruits added: {$result['fruits_count']}",
                "Vegetables added: {$result['vegetables_count']}"
            ]);

            if (!empty($result['errors'])) {
                $io->warning('Some items had errors:');
                foreach ($result['errors'] as $error) {
                    $io->text("- {$error}");
                }
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }
    }
} 