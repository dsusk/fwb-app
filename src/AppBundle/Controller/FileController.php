<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class FileController extends Controller
{
    /**
     * @param Request $request
     * @param string $name
     * @Route("/file/{name}", name="_file")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function downloadAction(Request $request, $name)
    {
        return $this->render('', array('name' => $name));
    }
}
