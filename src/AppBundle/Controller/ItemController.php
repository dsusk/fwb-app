<?php

namespace AppBundle\Controller;

use Solarium\Core\Query\Result\Result;
use Solarium\QueryType\Select\Query\FilterQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ItemController extends Controller
{

    /**
     * @Route("/lemma/{id}", name="_lemma")
     * @param string $id
     * @return string
     */
    public function indexAction(Request $request, $id)
    {

        $client = $this->get('solarium.client');
        $searchTerm = 'internal_id:' . $id;

        $originalSearchTerm = $request->get('q');
        $offset = (int)$request->get('start');

        $query = $client->createSelect();
        $query->setQuery($searchTerm);

        /** @var Result $resultset */
        $resultset = $client->select($query);
        if ($resultset->getNumFound() <> 1) {
            return $this->redirectToRoute('search', ['q' => $id], 301);
        }

        return $this->render('item/detail.html.twig',
            [
                'result' => $resultset->getDocuments()[0],
                'documents' => $this->getResultsFor($originalSearchTerm, $offset),
                'offset' => $offset,
                'searchTerm' => $originalSearchTerm
            ]);
    }

    /**
     * @param string $term
     * @param int $offset
     * @return Result
     */
    protected function getResultsFor($term, $offset)
    {

        $client = $this->get('solarium.client');
        $solrSearchTerm = 'lemma:' . $term . '*';
        $query = $client->createSelect();
        $query->setQuery($solrSearchTerm);

        // get filter query component
        $fq = new FilterQuery();
        $fq->setKey('type');
        $fq->setQuery('type:artikel');
        $query->addFilterQuery($fq);
        $query->setStart($this->getOffset($offset));

        return $client->select($query);

    }

    /**
     * @param $offset
     * @return int
     */
    protected function getOffset($offset)
    {
        if ($offset > 0) {
            $offset = --$offset;
        }

        return $offset;
    }

}
