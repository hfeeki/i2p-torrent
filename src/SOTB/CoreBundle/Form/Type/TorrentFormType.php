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
            'help_block'  => 'The title of the torrent file your are requesting.'
        ))
            ->add('description', 'purified_textarea', array(
            'required'    => false,
            'help_block'  => 'Your own text description of the torrent.'
        ))
            ->add($hash)
            ->add('filename', 'file', array(
            'required'    => false,
            'help_block'  => 'The .torrent file downloaded from the regular Internet.'
        ))
            ->add('categories', 'document', array(
            'required'      => false,
            'class'         => 'SOTBCoreBundle:Category',
            'query_builder' => function(DocumentRepository $dm)
            {
                return $dm->createQueryBuilder();
            },
            'multiple'      => true,
            'expanded'      => true
        ))
            ->add('format', 'text', array('required' => false, 'max_length' => 10, 'help_block'  => 'The file format of this torrent (mp3, avi, etc.).'))
            ->add('language', 'language', array('required' => false, 'preferred_choices' => array('en', 'ru', 'fr', 'de', 'es', 'ar'), 'help_block'  => 'The language of this version of the file.'));
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
