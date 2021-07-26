<?php

namespace App\Controller;

use App\Form\UserFormType;
use App\Security\EmailVerifier;
use Symfony\Component\Mime\Address;
use App\Form\ChangePasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AccountController extends AbstractController
{

    private $emailVerifier;
    
    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }
    
    /**
     * @Route("/account", name="app_account")
     */
    public function index(): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('error' , 'You are not logged');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('account/show.html.twig');
    }

    /**
     * @Route("/reverify/email", name="app_reverify_email")
     */
    public function SendVerifyUserEmail(Request $request ): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('error' , 'You are not logged');
            return $this->redirectToRoute('app_login');
        }

        
        $this->addFlash('success', 'Un mail vous a été envoyé');
            if ($this->getUser() && !($this->getUser()->isVerified())) {
            $user = $this->getUser();
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
            (new TemplatedEmail())
                ->from(new Address(
                    $this->getParameter('app.mail_from_address'), 
                    $this->getParameter('app.mail_from_name')
                    ))
                ->to($user->getEmail())
                ->subject('Please Confirm your Email')
                ->htmlTemplate('emails/registration/confirmation_email.html.twig')
            );

            return $this->redirectToRoute('app_home');
        }
    }

    /**
     * @Route("/account/edit", name="app_account_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, EntityManagerInterface $em) :Response {

        if (!$this->getUser()) {
            $this->addFlash('error' , 'You are not logged');
            return $this->redirectToRoute('app_login');
        }



        $user = $this->getUser();
        
        $form = $this->createForm(UserFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
      
           $em->flush();

           $this->addFlash('success','Account updated successfully');

           return $this->redirectToRoute('app_account');
        }
        

        return $this->render('account/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

        /**
     * @Route("/account/change-password", name="app_account_change_password", methods={"GET","POST"})
     */
    public function changePassword(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('error' , 'You are not logged');
            return $this->redirectToRoute('app_login');
        }

      
        $user = $this->getUser();

        $form = $this->createForm(ChangePasswordFormType::class);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $encodedPassword = $passwordHasher->hashPassword($user,$form->get('plainPassword')->getData());
            $user->setPassword($encodedPassword);
            $em->flush();
 
            $this->addFlash('success','Password updated successfully');
 
            return $this->redirectToRoute('app_account');
         }

        return $this->render('account/change_password.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
