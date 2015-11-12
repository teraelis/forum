<?php
namespace TerAelis\StatistiquesBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DailyStatsCommand extends ContainerAwareCommand {
    protected function configure()
    {
        $this
            ->setName('teraelis:stats:daily_stats')
            ->setDescription('Gets the stats of the previous day');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Début...');
        $dateStart = \microtime("now");

        $container = $this->getContainer();

        $dailyStatsService = $container->get('ter_aelis_statistiques.daily_stats');
        $dailyStatsService->saveDaily();

        $dateEnd = \microtime("now");
        $output->writeln('Fin');
        $output->writeln('Durée : '.($dateEnd - $dateStart).'s');
    }

}