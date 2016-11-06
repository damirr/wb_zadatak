<?php

namespace AppBundle\Controller;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Entity\Authors;
use AppBundle\Entity\Comments;

class DefaultController extends Controller
{
    /**
     * @Route("/add-comment", name="add-comment")
     */
    public function addCommentAction(Request $request)
    {
        
		$date = date("Y-m-d H:i:s");
        $author = new Authors();
        $comment = new Comments();
        $comment->setCreated(new \DateTime($date));
        
        
        $form = $this->createFormBuilder()
            ->add('name', TextType::class)
            ->add('text', TextType::class)
            ->add('save', SubmitType::class, array('label' => 'Save'))
            ->getForm();

        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $name = $form['name']->getData();
            $text = $form['text']->getData();
            
            $author->setName($name);
            $comment->setText($text);
            $comment->setAName($name);
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($author);
            $em->persist($comment);
            $em->flush();
			
			return $this->redirectToRoute('add-comment');
		}
		// replace this example code with whatever you need
        return $this->render('default/add-comment.html.twig', array('form'=>$form->createView()));
    }
	
	/**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $em    = $this->get('doctrine.orm.entity_manager');
		$dql   = "SELECT DISTINCT a.name FROM AppBundle:Authors a ORDER BY a.name ASC";
		$query = $em->createQuery($dql);
		
		$list = $query->getResult();
		
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', array('list'=>$list));
    }
	
	    /**
     * @Route("/{name}", name="name")
     */
    public function showAction (Request $request, $name) {
        $em = $this->get('doctrine.orm.entity_manager');
        //$qb = $em->createQuery('SELECT c FROM AppBundle:Comments c WHERE EXISTS(SELECT DISTINCT a.name FROM AppBundle:Authors a WHERE a.name=k.author) ORDER BY k.vrijeme DESC');
        $qb = $em->createQuery('SELECT c.text, c.aName, c.created FROM AppBundle:Comments c WHERE c.aName = :name');
		$qb->setParameter('name', $name);
		
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate($qb, $request->query->getInt('page', 1),
		$request->query->getInt('limit', 5));
        
        
        return $this->render('default\showname.html.twig', array('pagination' => $pagination));
        
	 }
}
