<?php

namespace AppBundle\Controller;

use Solarium\Core\Query\Result\Result;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class ItemController extends Controller
{

    /**
     * @Route("/lemma/{id}", name="_lemma")
     * @param string $id
     * @return string
     */
    public function indexAction($id)
    {

        $client = $this->get('solarium.client');
        $searchTerm = 'internal_id:' . $id;

        $query = $client->createSelect();
        $query->setQuery($searchTerm);

        /** @var Result $resultset */
        $resultset = $client->select($query);
        if ($resultset->getNumFound() <> 1) {
            return $this->redirectToRoute('search', ['q' => $id], 301);
        }

        return $this->render('item/detail.html.twig', ['result' => $resultset->getDocuments()]);
    }
}
