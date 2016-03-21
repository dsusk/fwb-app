<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Solarium\QueryType\Select\Query\FilterQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{

    /**
     * @var \Solarium\Core\Client\Client
     */
    protected $client;

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        return $this->render('search/index.html.twig',
            [
                'searchForm' => $this->getSearchForm()->createView()
            ]
        );
    }

    /**
     * @return \Symfony\Component\Form\Form
     */
    protected function getSearchForm()
    {

        /** @var FormBuilder $form */
        $form = $this
            ->get('form.factory')
            ->createNamedBuilder('', FormType::class, [], ['csrf_protection' => false])
            ->add('q', SearchType::class, [
                'attr' => [
                    'placeholder' => $this->get('translator')->trans('dictionary search'),
                ],
                'label_attr' => [
                    'class' => 'sr-only'
                ]
            ])
            ->add('search', SubmitType::class, [
                'label' => $this->get('translator')->trans('search'),
            ])
            ->setMethod('GET')
            ->setAction($this->generateUrl('search'));

        return $form->getForm();
    }


    /**
     * @Route("/search", name="search")
     */
    public function searchAction(Request $request)
    {

        $form = $this->getSearchForm();
        $form->handleRequest($request);

        if (!$form->isSubmitted() && !$form->isValid()) {
            return $this->redirectToRoute('homepage');
        }

        $this->client = $this->get('solarium.client');

        $client = $this->client;

        $searchTerm = $form->get('q')->getNormData();

        $solrSearchTerm = 'lemma:' . $searchTerm . '*';

        $paginator = $this->get('knp_paginator');

        // todo move into config
        $rows = 20;
        $currentPage = (int)$request->get('page') ?: 1;

        $offset = ($currentPage - 1) * $rows;

        $query = $client->createSelect();

        $query->setStart($offset);
        $query->setRows($rows);

        $query->setQuery($solrSearchTerm);

        // get highlighting component and apply settings
        $hl = $query->getHighlighting();
        $hl->setFields('article_html');
        $hl->setSimplePrefix('<em>');
        $hl->setSimplePostfix('</em>');

        // get filter query component
        $fq = new FilterQuery();
        $fq->setKey('type');
        $fq->setQuery('type:artikel');
        $query->addFilterQuery($fq);

        $pagination = $paginator->paginate(
            [
                $this->client, $query
            ],
            $currentPage,
            $rows
        );

        return $this->render('search/results.html.twig', [
            'searchTerm' => $searchTerm,
            'highlightResults' => $this->client->select($query)->getHighlighting(),
            'results' => $pagination,
            'searchForm' => $form->createView(),
            'offset' => $offset
        ]);

    }

}
