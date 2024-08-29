<?php

namespace OpenCartImporter\Command;

use OpenCartImporter\Services\ProductImport;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ProductImportCommand extends Command
{
    protected static $defaultName = 'app:product-import';
    protected static $defaultDescription = 'Imports products from an Excel file into the database.';

    private $productImport;

    public function __construct(ProductImport $productImport)
    {
        parent::__construct();

        $this->productImport = $productImport;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('filePath', InputArgument::REQUIRED, 'The path to the Excel file to import')
            ->setHelp('This command allows you to import products from an Excel file into your database...')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filePath = $input->getArgument('filePath');

        if (!file_exists($filePath)) {
            $io->error(sprintf('The file "%s" does not exist.', $filePath));
            return Command::FAILURE;
        }

        $io->title('Starting Product Import');
        
        try {
            $this->productImport->import($filePath);
            $io->success('Product import completed successfully.');
        } catch (\Exception $e) {
            $io->error('An error occurred during import: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}