<?php
namespace Rest\Bundles\ApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RestTestController extends Controller
{
    public function getTestAction($test)
    {
        return array('test'=>'test', 'number'=> 1, 'arrray' => array('test1'=>'success'), "inputparam" => $test);
    }
}
