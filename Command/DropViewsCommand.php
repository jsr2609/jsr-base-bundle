<?php

namespace JSR\BaseBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name:'jsr:drop-views')]
class DropViewsCommand extends Command
{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp('Drop views');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $sqlSelectViews = "SELECT 'DROP VIEW ' || 'IF EXISTS ' || table_schema || '.' || table_name || ';' as sql
        FROM information_schema.views
       WHERE table_schema NOT IN ('pg_catalog', 'information_schema')
         AND table_name !~ '^pg_';";

        $connection = $this->entityManager->getConnection();
        $resp = $connection->executeQuery($sqlSelectViews);
        $data = $resp->fetchAllAssociative();
        $cantidad = 0;
        


        $sql = "";

        foreach ($data as $key => $item) {
            $sql = $sql.$item['sql'];
            $io->writeln($item['sql']);
            $cantidad = $cantidad + 1;
        }

        if($sql != "") {
            try {
                $connection->executeStatement($sql);
            } catch (\Throwable $th) {
                $io->error($th->getMessage());
                return Command::FAILURE;
            }
        }

        

        $io->success(\sprintf("Se eliminarion %d vistas.", $cantidad));

        return Command::SUCCESS;
    }
}
