<?php

namespace Rest\Bundles\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Guzzle\Service\Client;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $client = new \Guzzle\Service\Client();
        $req = $client->get('http://google.com');
        $response = $req->send();

        //print_r($response);
        return $this->render('WebBundle:Default:index.html.twig',
            array('content' => $response));
    }
}
