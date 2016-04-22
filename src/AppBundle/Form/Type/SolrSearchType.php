<?php
namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Solr Search form
 */
class SolrSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('q', SearchType::class, [
                'attr' => [
                    'placeholder' => 'dictionary search',
                    'class' => 'typeahead'
                ],
                'label_attr' => [
                    'class' => 'sr-only'
                ]
            ])
            ->add('search', SubmitType::class, [
                'label' => 'search',
            ])
            ->setMethod('GET')
            ->setAction($this->generateUrl('search'));
    }
}
