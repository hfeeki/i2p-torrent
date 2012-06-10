<?php

namespace SOTB\CoreBundle\DataFixtures\MondoDB;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;

use SOTB\CoreBundle\Document\Torrent;
use SOTB\CoreBundle\Document\Peer;

/**
 * @author Matt Drollette <matt@drollette.com>
 */
class LoadTorrentData implements FixtureInterface
{

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $faker = FakerFactory::create();

        $torrent = new Torrent();
        $torrent->setHash('12345678901234567890');
        $torrent->setName($faker->company);

        for ($i = 1; $i < 50; $i++) {
            $peer = new Peer();
            $peer->setIp($faker->ipv4);
            $peer->setPort($faker->randomNumber(5));
            $peer->setPeerId($faker->bothify('????####?#?##?#??###?#?#?#?#?#??#?#?#'));

            $torrent->addPeer($peer);

            $manager->persist($peer);
        }

        $manager->persist($torrent);
        $manager->flush();
    }
}