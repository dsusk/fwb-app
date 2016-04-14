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

        $query = $client->createSelect();
        $query->setQuery($searchTerm);

        /** @var Result $resultset */
        $resultset = $client->select($query);
        if ($resultset->getNumFound() <> 1) {
            return $this->redirectToRoute('search', ['q' => $id], 301);
        }

        return $this->render('item/detail.html.twig',
            [
                'result' => $resultset->getDocuments(),
                'documents' => $this->getResultsFor($originalSearchTerm),
                'searchTerm' => $originalSearchTerm
            ]);
    }

    /**
     * @param string $searchTerm
     * @return Result
     */
    protected function getResultsFor($searchTerm)
    {
        $client = $this->get('solarium.client');
        $solrSearchTerm = 'lemma:' . $searchTerm . '*';
        $query = $client->createSelect();
        $query->setQuery($solrSearchTerm);

        // get filter query component
        $fq = new FilterQuery();
        $fq->setKey('type');
        $fq->setQuery('type:artikel');
        $query->addFilterQuery($fq);

        return $client->select($query);

    }

}
