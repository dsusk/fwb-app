<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Solarium\QueryType\Select\Query\FilterQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
            ->createNamedBuilder(
                null,
                FormType::class,
                [],
                [
                    'csrf_protection' => false
                ]
            )
            ->add('q', SearchType::class, [
                'attr' => [
                    'placeholder' => $this->get('translator')->trans('dictionary search'),
                    'class' => 'typeahead'
                ],
                'label_attr' => [
                    'class' => 'sr-only'
                ]
            ])
            ->add('full-text', CheckboxType::class, [
                'label' => $this->get('translator')->trans('full-text'),
                'required' => false,
            ])
            ->add('page', HiddenType::class)
            ->add('type', HiddenType::class)
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

        $requestType = $request->get('type');

        if ($requestType === 'ref') {
            $solrSearchTerm = 'internal_id:' . $searchTerm . '*';
        } else {
            $searchBuilder = [];
            $searchBuilder[] = 'lemma:' . $searchTerm . '*';
            if ($form->has('full-text')) {
                $searchBuilder[] = 'article_html:' . $searchTerm . '*';
            }
            $solrSearchTerm = implode(' OR ', $searchBuilder);
        }
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

        $facetSet = $query->getFacetSet();
        $facetSet->createFacetField('type_of_word')->setField('type_of_word');

        $pagination = $paginator->paginate(
            [
                $this->client,
                $query
            ],
            $currentPage,
            $rows
        );

        $solrQuery = $this->client->select($query);

        return $this->render('search/results.html.twig', [
            'searchTerm' => $searchTerm,
            'highlightResults' => $solrQuery->getHighlighting(),
            'results' => $pagination,
            'facets' => $solrQuery->getFacetSet()->getFacet('type_of_word'),
            'searchForm' => $form->createView(),
            'offset' => $offset,
            'currentPage' => $currentPage
        ]);

    }

    /**
     * @Route("/autocomplete", name="_autocomplete")
     */
    public function autocompleteAction(Request $request)
    {
        $client = $this->get('solarium.client');

        // get a suggester query instance
        $query = $client->createSuggester();
        $query->setQuery($request->get('term'));
        $query->setDictionary('suggest');

        $query->setCount(10);
        $query->setCollate(true);

        // this executes the query and returns the result
        $resultset = $client->suggester($query);

        $suggestions = [];

        foreach ($resultset as $term => $termResult) {
            foreach ($termResult as $result) {
                $suggestions[] = $result;
            }
        }

        $response = new Response();
        $response->setContent(json_encode($suggestions));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

}
