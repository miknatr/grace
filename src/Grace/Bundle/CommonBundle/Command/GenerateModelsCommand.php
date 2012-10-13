<?php

namespace Grace\Bundle\CommonBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Grace\Bundle\CommonBundle\Generator\ModelsGenerator;

class GenerateModelsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('grace:generate_models')
            ->setDescription('Grace models and validators generation');
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this
            ->getContainer()
            ->get('grace_generator')
            ->generate();

        $output->writeln('Done');
    }
}