<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Solarium\QueryType\Select\Query\FilterQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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

        $this->client = $this->get('solarium.client');

        // replace this example code with whatever you need
        return $this->render('search/index.html.twig', array(
            'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
        ));
    }

    /**
     * @Route("/search", name="search")
     *
     */
    public function searchAction()
    {

        $this->client = $this->get('solarium.client');

        $request = Request::createFromGlobals();

        $client = $this->client;

        $searchTerm = 'lemma:' . $request->get('q') . '*';

        // todo move into config
        $rows = 20;
        $currentPage = (int)$request->get('page') ?: 1;

        $offset = ($currentPage - 1) * $rows;

        $query = $client->createSelect();

        $query->setStart($offset);
        $query->setRows($rows);

        $query->setQuery($searchTerm);

        // get the facetset component
        $facetSet = $query->getFacetSet();

        $facetSet->createFacetField('type_of_word');

        $fq = new FilterQuery();
        $fq->setKey('type');
        $fq->setQuery('type:artikel');
        $query->addFilterQuery($fq);
        $resultset = $client->select($query);


        $facets = $resultset->getFacetSet()->getFacet('type_of_word');

        /*
        $results = new LengthAwarePaginator($resultset->getDocuments(), $resultset->getNumFound(), $rows, null, ['path' => 'search']);

        return view('search.results',
            [
                'searchTerm' => $request->get('q'),
                'resultCount' => $resultset->getNumFound(),
                'results' => $results,
                'firstItem' => $results->firstItem(),
                'facets' => $facets
            ]

        */
        return $this->render('search/results.html.twig', [
                    'searchTerm' => $request->get('q'),
            'resultCount' => $resultset->getNumFound()
                ]);

    }

}
