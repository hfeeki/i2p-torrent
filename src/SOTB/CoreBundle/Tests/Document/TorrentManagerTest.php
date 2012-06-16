<?php

namespace SOTB\CoreBundle\Document;

class TorrentManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $torrentManager;

    public function testFindUserByUsername()
    {
        $this->userManager->expects($this->once())
            ->method('findUserBy')
            ->with($this->equalTo(array('usernameCanonical' => 'jack')));
        $this->userManager->expects($this->once())
            ->method('canonicalizeUsername')
            ->with($this->equalTo('jack'))
            ->will($this->returnValue('jack'));

        $this->userManager->findUserByUsername('jack');
    }

//
//    public function testLoadUserByUsernameWithExistingUser()
//    {
//        $userMock = $this->getMock('FOS\UserBundle\Document\User', array(), array('sha1'));
//
//        $manager = $this->getMockBuilder('FOS\UserBundle\Document\UserManager')
//            ->disableOriginalConstructor()
//            ->setMethods(array('findUserByUsername'))
//            ->getMock();
//
//        $manager->expects($this->once())
//            ->method('findUserByUsername')
//            ->with($this->equalTo('jack'))
//            ->will($this->returnValue($userMock));
//
//        $manager->loadUserByUsername('jack');
//    }
//
//    /**
//     * @expectedException Symfony\Component\Security\Core\Exception\UsernameNotFoundException
//     */
//    public function testLoadUserByUsernameWithMissingUser()
//    {
//        $manager = $this->getMockBuilder('FOS\UserBundle\Document\UserManager')
//            ->disableOriginalConstructor()
//            ->setMethods(array('findUserByUsername'))
//            ->getMock();
//
//        $manager->expects($this->once())
//            ->method('findUserByUsername')
//            ->with($this->equalTo('jack'))
//            ->will($this->returnValue(null));
//
//        $manager->loadUserByUsername('jack');
//    }

    protected function setUp()
    {
        $this->torrentManager = $this->getManagerMock();
    }

    protected function tearDown()
    {
        unset($this->torrentManager);
    }

    protected function getManagerMock()
    {
        return $this->getMockBuilder('SOTB\CoreBundle\TorrentManager')
            ->disableOriginalConstructor()
            ->setMethods(array('process', 'getFileData', 'get_tld_from_url'))
            ->getMock();
    }
}
