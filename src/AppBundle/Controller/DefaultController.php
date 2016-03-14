<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Solarium\QueryType\Select\Query\FilterQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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

        $this->client = $this->get('solarium.client');

        // replace this example code with whatever you need
        return $this->render('search/index.html.twig', array(
            'base_dir' => realpath($this->container->getParameter('kernel.root_dir') . '/..'),
        ));
    }

    /**
     * @Route("/search", name="search")
     *
     */
    public function searchAction(Request $request)
    {

        $this->client = $this->get('solarium.client');

        $client = $this->client;

        $searchTerm = 'lemma:' . $request->get('q') . '*';

        $paginator = $this->get('knp_paginator');

        // todo move into config
        $rows = 20;
        $currentPage = (int)$request->get('page') ?: 1;

        $offset = ($currentPage - 1) * $rows;

        $query = $client->createSelect();

        $query->setStart($offset);
        $query->setRows($rows);

        $query->setQuery($searchTerm);

        $fq = new FilterQuery();
        $fq->setKey('type');
        $fq->setQuery('type:artikel');
        $query->addFilterQuery($fq);

        $pagination = $paginator->paginate(
            [$this->client, $query],
            $currentPage,
            $rows
        );

        return $this->render('search/results.html.twig', [
            'searchTerm' => $request->get('q'),
            'results' => $pagination,
        ]);

    }

}
