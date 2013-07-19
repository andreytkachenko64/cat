<?php
/*
 * This file is part of ONP.
 *
 * Copyright (c) 2013 Opensoft (http://opensoftdev.com)
 *
 * The unauthorized use of this code outside the boundaries of
 * Opensoft is prohibited.
 *
 */


namespace AT\CoreBundle\Command;

use AT\CoreBundle\Interfaces\GeneratorInterface;
use AT\CoreBundle\Interfaces\ParserInterface;
use AT\CoreBundle\Service\NodeService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * AT\CoreBundle\Command\ScanCommand
 *
 * @author Andrey Tkachenko <andrey.tkachenko@opensoftdev.ru>
 */
class ScanCommand extends ContainerAwareCommand
{
    const AAA = 1;


    protected function configure()
    {
        $this
            ->setName('scan')
            ->setDescription('Scan Project Files')
            ->addArgument('project', InputArgument::OPTIONAL, 'Project to scan')
            ->addOption('deep', null, InputOption::VALUE_NONE, 'Deep scanning');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var string $project */
        $project = $input->getArgument('project');
        $a = new \stdClass();

        if ($project) {
            $text = 'Hello ' . $project;
        } else {
            $text = 'Hello';
        }

        if ($input->getOption('deep')) {
            $text .= strtoupper($text);
        }

        $parser = $this->getPHPParser();
        $node = $parser->parseModule(file_get_contents(__FILE__/*'/home/andrey/workspace/cat/src/AT/CoreBundle/Generator/PHPGenerator.php'*/));

        $text = print_r($this->getNodeService()->storeNode($node), 1);

        //$text = $this->getPHPGenerator()->generate($stats);

        //$this->getNodeService()->storeModuleNodes($file, $stats);

        $output->writeln($text);
    }

    /**
     * @return ParserInterface
     */
    protected function getPHPParser()
    {
        return $this->getContainer()->get('at_core.parser.php_parser');
    }

    /**
     * @return NodeService
     */
    protected function getNodeService()
    {
        return $this->getContainer()->get('at_core.service.node_service');
    }

    /**
     * @return GeneratorInterface
     */
    protected function getPHPGenerator()
    {
        return $this->getContainer()->get('at_core.generator.php_generator');
    }
}