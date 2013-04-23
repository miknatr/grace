<?php

namespace Grace\Bundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CleanCacheCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('grace:clean_cache')
            ->setDescription('Cleans cache (such as memcache)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->get('cache')->clean();
        $output->writeln("Done");
    }
}
