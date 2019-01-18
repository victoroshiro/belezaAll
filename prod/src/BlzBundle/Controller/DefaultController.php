<?php

namespace BlzBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        return $this->render('BlzBundle:Default:index.html.twig');
    }

    /**
     * @Route("/recuperar-senha/{hash}/")
     */
    public function passwordRecoveryPageAction($hash)
    {
        $em = $this->getDoctrine()->getManager();

        $valid = false;

        $passwordRec = $em->getRepository("BlzBundle:PasswordRecovery")->findOneByHash($hash);

        if(!empty($passwordRec)){
            $now = new \Datetime;
            
            $diff = $now->diff($passwordRec->getDatetime());
            $hour = $diff->h + $diff->days * 24;
    
            if($hour <= 8){
                $valid = true;
            }
        }

        return $this->render('BlzBundle:Default:password-recovery.html.twig', array(
            "passwordRec" => $passwordRec,
            "hash" => $hash,
            "valid" => $valid
        ));
    }
}
