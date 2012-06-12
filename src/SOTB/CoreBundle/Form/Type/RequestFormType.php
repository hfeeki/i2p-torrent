<?php

namespace SOTB\CoreBundle\Form\Type;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Sales order form type.
 *
 * @author Matt Drollette <matt@drollette.com>
 */
class RequestFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', 'text', array(
            'required' => true
        ))
            ->add('description', 'purified_textarea', array(
            'required' => false
        ))
            ->add('hash', 'text', array(
            'required' => false
        ));
    }


    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SOTB\CoreBundle\Document\Request',
            'validation_groups' => array('request')
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'request';
    }
}
