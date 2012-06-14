<?php

namespace SOTB\CoreBundle\DataFixtures\MondoDB;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;

use SOTB\CoreBundle\Document\Torrent;
use SOTB\CoreBundle\Document\Peer;
use SOTB\CoreBundle\Document\Category;

/**
 * @author Matt Drollette <matt@drollette.com>
 */
class LoadTorrentData implements FixtureInterface
{

    private $categories = array(
        'Animations',
        'Audio Books',
        'Books',
        'Games',
        'Graphic Novels',
        'Movies',
        'Music',
        'Other',
        'Pictures',
        'Podcasts',
        'Porn',
        'Television'
    );

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $faker = FakerFactory::create();
        $hash = '1234567890123456789';

        // create the categories
        foreach ($this->categories as $category) {
            $cat = new Category();
            $cat->setName($category);

            for ($c = 11; $c <= 15; $c++) {
                $torrent = new Torrent();
                $torrent->setHash($hash . $c);
                $torrent->setName($faker->company);
                $torrent->setTitle($faker->company);
                $torrent->setDescription($faker->paragraph);

                $torrent->addCategory($cat);

                $manager->persist($torrent);
            }

            $manager->persist($cat);
        }


        for ($a = 1; $a <= 4; $a++) {
            $torrent = new Torrent();
            $torrent->setHash($hash . $a);
            $torrent->setName($faker->company);
            $torrent->setTitle($faker->company);
            $torrent->setDescription($faker->paragraph);

            for ($i = 1; $i < 50; $i++) {
                $peer = new Peer();
                $peer->setIp($faker->ipv4);
                $peer->setPort($faker->randomNumber(5));
                $peer->setPeerId($faker->bothify('????####?#?##?#??###?#?#?#?#?#??#?#?#'));

                $torrent->addPeer($peer);

                $manager->persist($peer);
            }

            $manager->persist($torrent);
        }

        $manager->flush();
    }
}