<?php

namespace SOTB\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Matt Drollette <matt@drollette.com>
 */
class TrackerUpdateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('tracker:update')
            ->setDescription('Update all seeder and leecher stats from active peers.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $dm \Doctrine\ODM\MongoDB\DocumentManager */
        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        $tracker = $this->getContainer()->get('tracker');

        $torrents = $dm->getRepository('SOTBCoreBundle:Torrent')->findAllNeedingUpdate();

        $processed = count($torrents);
        $seeders = 0;
        $leechers = 0;

        foreach ($torrents as $torrent) {

            // update the torrent stats
            $peer_stats = $tracker->getPeerStats($torrent);
            $torrent->setSeeders($peer_stats['complete']);
            $seeders += $peer_stats['complete'];

            $torrent->setLeechers($peer_stats['incomplete']);
            $leechers += $peer_stats['incomplete'];

            $torrent->setLastUpdate(new \DateTime());

            $dm->persist($torrent);
        }

        $dm->flush();

        $output->writeln('Processed '. $processed .' torrents with a total of '. $seeders .' seeders and '. $leechers .' leechers.');
    }
}