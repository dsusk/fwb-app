<?php

namespace AppBundle\Controller;

use Solarium\Core\Query\Result\Result;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SourceController extends Controller
{

    /**
     * @Route("/source/{id}", name="_source")
     * @param string $id
     * @return string
     */
    public function indexAction($id)
    {

        $client = $this->get('solarium.client');
        $searchTerm = 'id:' . $id;

        $query = $client->createSelect();
        $query->setQuery($searchTerm);

        $response = new Response();

        /** @var Result $resultset */
        $resultset = $client->select($query);
        if ($resultset->getNumFound() <> 1) {
            $message = $this->get('translator')->trans('source not found');
        } else {
            $message = $resultset->getDocuments()[0]['source_html'];
        }

        $response->setContent($message);

        return $response;
    }

}
