<?php
namespace TerAelis\ForumBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints\DateTime;

class CategoryCommand extends ContainerAwareCommand {
    protected function configure()
    {
        $this
            ->setName('teraelis:forum:reset_stats')
            ->setDescription('Reset all the stats of all the categories');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Début...');
        $dateStart = \microtime("now");
        $container = $this->getContainer();
        $em = $container->get('doctrine.orm.entity_manager');
        $em->beginTransaction();

        $container->get('ter_aelis_forum.post_statistics')
            ->refreshPosts();

        $container->get('ter_aelis_forum.post_statistics')
            ->refreshCategories();

        $em->commit();
        $dateEnd = \microtime("now");
        $output->writeln('Fin');
        $output->writeln('Durée : '.($dateEnd - $dateStart));
    }

}