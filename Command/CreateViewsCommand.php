<?php

namespace App\Command;

use Symfony\Component\Finder\Finder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name:'app:create-views')]
class CreateViewsCommand extends Command
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }
    

    protected function configure(): void
    {
        $this
            ->setHelp('Create views');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        

        $finder = new Finder();
        $finder->in(__DIR__ . '/../../sql/views');
        $finder->name('*.sql');
        $finder->files();
        $finder->sortByName();

        foreach ($finder as $file) {
            $io->writeln("Importing: {$file->getBasename()} ");
            try {
                $sql = $file->getContents();

                $connection = $this->entityManager->getConnection();
                $connection->executeStatement($sql);
                
            } catch (\Throwable $th) {
                $io->error($file->getBasename().' '.$th->getMessage());

                return Command::FAILURE;
            }
        }

        $io->success('Views Created');

        return Command::SUCCESS;
    }
}
