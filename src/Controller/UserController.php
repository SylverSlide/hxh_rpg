<?php

namespace App\Controller;

use App\Security\EmailVerifier;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
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
        return $this->render('user/account.html.twig');
    }

            /**
     * @Route("/reverify/email", name="app_reverify_email")
     */
    public function SendVerifyUserEmail(Request $request ): Response
    {
        
        $session = $request->getSession();
        
        $session->set('confirmation-mail', true);
        
        $confirmationMail = $session->get('confirmation-mail');

        if($confirmationMail){
            $this->addFlash('error', 'Un mail vous a déjà été envoyé, vérifié vos spams');
            return $this->render('user/account.html.twig');
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

            return $this->render('user/account.html.twig');
        }
    }
}
