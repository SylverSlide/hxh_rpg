<?php

namespace App\Controller;

use App\Entity\Pin;
use App\Form\PinType;
use App\Repository\PinRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PinsController extends AbstractController
{
    
    /**
     * @Route("/", name="app_home" , methods={"GET"})
     */
    public function index(PinRepository $pinRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $queryBuilder = $pinRepository->findBy([], ['createdAt' => 'ASC']);

        $pins = $paginator->paginate(
            $queryBuilder, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            9/*limit per page*/
        );


        return $this->render('pins/index.html.twig', compact('pins'));
    }
    
    /**
     * @Route("/pins/{id}", name="app_pins_show", methods="GET")
     */
    public function show(Pin $pin): Response{

        return $this->render('pins/show.html.twig', compact('pin'));
    }
    
    /**
     * @Route("/pins/create", name="app_pins_create" ,priority=10, methods={"GET", "POST"})
     */
    public function create(Request $request, EntityManagerInterface $em, UserRepository $userRepo): Response
    {
        $pin = new Pin;

        $form = $this->createForm(PinType::class , $pin);

        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid()) {

            $pin->setUser($this->getUser());
            $em -> persist($pin);
            $em -> flush();

            $this->addFlash('success', 'Pin successfully created!');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('pins/create.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/pins/{id}/edit",priority=9, name="app_pins_edit" , methods={"GET", "PUT"})
     */
    public function edit(Pin $pin ,Request $request, EntityManagerInterface $em): Response{

        $form = $this->createForm(PinType::class, $pin , [
            'method' => 'PUT'
        ]);

        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid()) {
            $em -> flush();

            $this->addFlash('success', 'Pin successfully updated!');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('pins/edit.html.twig', [
            'pin' => $pin,
            'form' => $form->createView()
        ]);

    }

    /**
     * @Route("/pins/{id}",priority=5, name="app_pins_delete" , methods={"DELETE"})
     */
    public function delete(Pin $pin ,Request $request, EntityManagerInterface $em): Response{

      $token =  $request->request->get('csrf_token');
    
    if ($this->isCsrfTokenValid('pins_deletion' . $pin->getId(), $token)){

        $em->remove($pin);
        $em->flush();

        $this->addFlash('info', 'Pin successfully deleted!');
    }

        return $this->redirectToRoute('app_home');
    }


}
