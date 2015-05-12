<?php

namespace Acme\DemoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Acme\DemoBundle\Form\ContactType;
use Acme\DemoBundle\Resources\lib\StockWatcher;

// these import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;



class DemoController extends Controller
{
    /**
     * @Route("/", name="_demo")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }
	

    /**
     * @Route("/hello/{name}", name="_demo_hello")
     * @Template(engine="php")
     */
    public function helloAction($name)
    {
    	
        $stockWatcher = new StockWatcher();
        $stockRecords = $stockWatcher->getFinalizedRecords();
        $timeInterval = array_keys($stockWatcher->getTimeInterval());
        
        print_r($stockRecords);
        return array('stockRecords' => $stockRecords, 'timeInterval' => $timeInterval);
		
	    // return $this->render(
	        // 'DemoBundle:Hello:hello.html.php',
	        // array('stockRecords' => $stockRecords, 'timeInterval' => $stockRecords)
	    // );
    }

    /**
     * @Route("/contact", name="_demo_contact")
     * @Template()
     */
    public function contactAction(Request $request)
    {
        $form = $this->createForm(new ContactType());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $mailer = $this->get('mailer');

            // .. setup a message and send it
            // http://symfony.com/doc/current/cookbook/email.html

            $request->getSession()->getFlashBag()->set('notice', 'Message sent!');

            return new RedirectResponse($this->generateUrl('_demo'));
        }

        return array('form' => $form->createView());
    }
}


