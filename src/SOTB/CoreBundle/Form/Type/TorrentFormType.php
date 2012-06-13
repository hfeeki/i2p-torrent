<?php

namespace SOTB\CoreBundle\Form\Type;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use SOTB\CoreBundle\Form\DataTransformer\MagnetToHashTransformer;

/**
 * Sales order form type.
 *
 * @author Matt Drollette <matt@drollette.com>
 */
class TorrentFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $hash = $builder->create('hash', 'text', array(
            'required'    => false,
            'help_block'  => 'torrent hash or magnet link'
        ))->addModelTransformer(new MagnetToHashTransformer());

        $builder
            ->add('title', 'text', array(
            'required'    => true,
            'help_block'  => 'the title of the torrent file your are requesting'
        ))
            ->add('description', 'purified_textarea', array(
            'required'    => false,
            'help_block'  => 'your own text description of the torrent'
        ))
            ->add($hash)
            ->add('filename', 'file', array(
            'required'    => false,
            'help_block'  => 'the .torrent file downloaded from the regular Internet'
        ));
    }


    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SOTB\CoreBundle\Document\Torrent'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'torrent';
    }
}
