<?php

namespace AppBundle\Controller;

use Knp\Component\Pager\Pagination\PaginationInterface;
use Solarium\Core\Query\Result\Result;
use Solarium\QueryType\Select\Query\FilterQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ItemController extends Controller
{

    /**
     * @Route("/lemma/{id}", name="_lemma")
     * @param Request $request
     * @param string $id
     * @return string
     */
    public function indexAction(Request $request, $id)
    {

        $client = $this->get('solarium.client');
        $paginator = $this->get('knp_paginator');

        $searchTerm = 'internal_id:' . $id;

        $offset = (int)$request->get('page');

        $query = $client->createSelect();
        $query->setQuery($searchTerm);

        /** @var Result $resultset */
        $resultset = $client->select($query);
        if ($resultset->getNumFound() <> 1) {
            return $this->redirectToRoute('search', ['q' => $id, 'type' => 'ref'], 301);
        }
        $currentPage = (int)$request->get('page') ?: 1;
        $rows = 20;

        $counter = $currentPage * $rows -$rows +1;

        $originalSearchTerm = $request->get('q');

        $pagination = null;

        if ($originalSearchTerm) {

            /** @var PaginationInterface $pagination */
            $pagination = $paginator->paginate(
                [
                    $client,
                    $this->getResultsFor($originalSearchTerm, $offset)
                ],
                $currentPage,
                $rows
            );

            $pagination->setPageRange(1);
            $pagination->setTemplate('Pagination/sidebar.html.twig');
        }

        return $this->render('item/detail.html.twig',
            [
                'result' => $resultset->getDocuments()[0],
                'definitionIndex' => $this->getDefinitionIndex($resultset->getDocuments()[0]['id']),
                'documents' => $pagination,
                'offset' => $offset,
                'searchTerm' => $originalSearchTerm,
                'currentPage' => $currentPage,
                'counter' => $counter,
            ]);
    }

    /**
     * @param string $documentId
     * @return Result
     */
    protected function getDefinitionIndex($documentId)
    {
        $client = $this->get('solarium.client');
        $solrSearchTerm = 'ref_id:' . $documentId . ' AND type:bedeutung';
        $query = $client->createSelect();
        $query->addSort('sense_number', $query::SORT_ASC);
        $query->setRows(50);
        $query->setQuery($solrSearchTerm);

        return $client->select($query)->getDocuments();
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

        return $query;

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
