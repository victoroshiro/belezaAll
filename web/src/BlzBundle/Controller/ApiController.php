<?php

namespace BlzBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use BlzBundle\Entity\User;
use BlzBundle\Entity\UserData;
use BlzBundle\Entity\Address;
use BlzBundle\Entity\UserAddress;
use BlzBundle\Entity\Scheduling;
use BlzBundle\Entity\SchedulingService;
use BlzBundle\Entity\ChatRoom;
use BlzBundle\Entity\Chat;
use BlzBundle\Entity\PasswordRecovery;
use BlzBundle\Entity\AwardRequest;
use BlzBundle\Entity\ProviderPayment;
use BlzBundle\Entity\Ad;
use BlzBundle\Entity\ProviderService;
use BlzBundle\Entity\Finance;
use BlzBundle\Entity\UserPoint;
use BlzBundle\Entity\AvailableDate;
use BlzBundle\Entity\AvailableTime;
use BlzBundle\Entity\ProviderServicePrice;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * @Route("/api")
 */
class ApiController extends Controller
{
    private $times = array("00:00", "00:30", "01:00", "01:30", "02:00", "02:30", "03:00", "03:30", "04:00", "04:30", "05:00", "05:30", "06:00", "06:30", "07:00", "07:30", "08:00", "08:30", "09:00", "09:30", "10:00", "10:30", "11:00", "11:30", "12:00", "12:30", "13:00", "13:30", "14:00", "14:30", "15:00", "15:30", "16:00", "16:30", "17:00", "17:30", "18:00", "18:30", "19:00", "19:30", "20:00", "20:30", "21:00", "21:30", "22:00", "22:30", "23:00", "23:30");
    private $serviceTimes = array(
        array("id" => 0, "time" => "00:30"), 
        array("id" => 1, "time" => "01:00"), 
        array("id" => 2, "time" => "01:30"), 
        array("id" => 3, "time" => "02:00"), 
        array("id" => 4, "time" => "02:30"), 
        array("id" => 5, "time" => "03:00"), 
        array("id" => 6, "time" => "03:30"), 
        array("id" => 7, "time" => "04:00"), 
        array("id" => 8, "time" => "04:30"), 
        array("id" => 9, "time" => "05:00"), 
        array("id" => 10, "time" => "05:30"), 
        array("id" => 11, "time" => "06:00"), 
        array("id" => 12, "time" => "06:30"), 
        array("id" => 13, "time" => "07:30"), 
        array("id" => 14, "time" => "08:00"), 
        array("id" => 15, "time" => "08:30"), 
        array("id" => 16, "time" => "09:30"), 
        array("id" => 17, "time" => "10:00"), 
        array("id" => 37, "time" => "20:00"), 
        array("id" => 57, "time" => "30:00"), 
        array("id" => 77, "time" => "40:00"), 
        array("id" => 97, "time" => "50:00"), 
        array("id" => 117, "time" => "60:00")
    );

    public function getTimesId($times)
    {
        $timesIds = array();

        for($i = 0; $i < count($this->times); $i = $i + 1){
            if(in_array($this->times[$i], $times)){
                $timesIds[] = $i;
            }
        }

        return $timesIds;
    }
    
    public function getContent($request)
    {
        $data = json_decode($request->getContent(), true);

        return !empty($data) ? $data : array();
    }

    public function getProfile($user)
    {
        if(preg_match('/^(\d{2})(\d{4})(\d{4})$/', $user->getUserData()->getPhone(), $matches)){
            $phoneFormated = '(' . $matches[1] . ') ' .$matches[2] . '-' . $matches[3];
        }
        else{
            $phoneFormated = $user->getUserData()->getPhone();
        }

        if(preg_match( '/^(\d{2})(\d{1})(\d{4})(\d{4})$/', $user->getUserData()->getCelphone(), $matches)){
            $celPhoneFormated = '(' . $matches[1] . ') ' .$matches[2] . $matches[3] . '-' . $matches[4];
        }
        else{
            $celPhoneFormated = $user->getUserData()->getCelphone();
        }

        if(preg_match('/^(\d{2})(\d{3})(\d{3})(\d{1})$/', $user->getUserData()->getRg(), $matches)){
            $rgFormated = $matches[1] .  "." . $matches[2] . "." . $matches[3] . "-" . $matches[4];
        }
        else{
            $rgFormated = $user->getUserData()->getRg();
        }

        if(preg_match('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', $user->getUserData()->getCpf(), $matches)){
            $cpfFormated = $matches[1] .  "." . $matches[2] . "." . $matches[3] . "-" . $matches[4];
        }
        else{
            $cpfFormated = $user->getUserData()->getCpf();
        }
        
        return json_encode(array(
            "id" => $user->getId(),
            "name" => $user->getName(),
            "email" => $user->getEmail(),
            "phone" => $phoneFormated,
            "celphone" => $celPhoneFormated,
            "rg" => $rgFormated,
            "cpf" => $cpfFormated,
            "social" => $user->getUserData()->getSocial(),
            "photo" => $user->getUserData()->getPhoto(),
            "privacy_accepted" => $user->getPrivacyAccepted()
        ));
    }

    /**
     * @Route("/states/")
     */
    public function statesAction()
    {
        $em = $this->getDoctrine()->getManager();

        $states = $em->getConnection()->prepare("SELECT id, name, uf FROM state");
        $states->execute();
        $states = $states->fetchAll();

        return new Response(json_encode($states));
    }

    /**
     * @Route("/state/{uf}/cities/")
     */
    public function citiesByStateAction($uf)
    {
        $em = $this->getDoctrine()->getManager();

        $cities = $em->getConnection()->prepare("SELECT city.id, city.name FROM city INNER JOIN state on city.state = state.id WHERE uf = :state");
        $cities->execute(array(
            "state" => $uf
        ));
        $cities = $cities->fetchAll();

        return new Response(json_encode($cities));
    }

    /**
     * @Route("/register/")
     * @Method("POST")
     */
    public function registerAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $name = $request->request->get("name");
        $phone = $request->request->get("phone");
        $celphone = $request->request->get("celphone");
        $rg = $request->request->get("rg");
        $cpf = $request->request->get("cpf");
        $email = $request->request->get("email");
        $password = $request->request->get("password");

        try{
            if(empty($name) || empty($celphone) || empty($rg) || empty($cpf) || empty($email) || empty($password)){
                throw new \Exception("Dados incompletos", 500);
            }

            $userType = $em->getRepository("BlzBundle:UserType")->findOneById(4);

            if(empty($userType)){
                throw new \Exception("Tipo de usuário não encontrado", 404);
            }

            $checkUser = $em->getRepository("BlzBundle:User")->findOneByEmail($email);

            if(!empty($checkUser)){
                throw new \Exception("Já existe um usuário com este email", 500);
            }

            $data = new UserData;

            if(!empty($phone)){
                $data->setPhone(preg_replace("/[^0-9]+/", "", $phone));
            }

            if(!empty($celphone)){
                $data->setCelphone(preg_replace("/[^0-9]+/", "", $celphone));
            }
            
            if(!empty($rg)){
                $data->setRg(preg_replace("/[^0-9]+/", "", $rg));
            }

            if(!empty($cpf)){
                $data->setCpf(preg_replace("/[^0-9]+/", "", $cpf));
            }

            $em->persist($data);

            $user = new User;
            $user->setName($name);
            $user->setEmail($email);
            $user->setPassword(md5($password));
            $user->setType($userType);
            $user->setUserData($data);
            $user->setPrivacyAccepted(true);

            $em->persist($user);

            $em->flush();

            $em->getConnection()->commit();
            return new Response($this->getProfile($user));
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            $em->getConnection()->rollBack();
            return new Response($message, $status);
        }
    }

    /**
     * @Route("/login/")
     * @Method("POST")
     */
    public function loginAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $email = $request->request->get("email");
        $password = $request->request->get("password");

        try{
            if(empty($email) || empty($password)){
                throw new \Exception("Dados incompletos", 500);
            }

            $user = $em->getRepository("BlzBundle:User")->findOneBy(array(
                "email" => $email,
                "password" => md5($password),
                "type" => $em->getRepository("BlzBundle:UserType")->findOneById(4)
            ));

            if(empty($user)){
                throw new \Exception("Login ou senha incorretos", 403);
            }

            if(empty($user->getActive())){
                throw new \Exception("Usuário bloqueado", 403);
            }

            return new Response($this->getProfile($user));
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            return new Response($message, $status);
        }
    }

    /**
     * @Route("/login/social/")
     * @Method("POST")
     */
    public function loginSocialAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $name = $request->request->get("name");
        $email = !empty($request->request->get("email")) ? $request->request->get("email") : $request->request->get("id");
        $photo = $request->request->get("photo");

        try{
            if(empty($name) || empty($email)){
                throw new \Exception("Dados incompletos", 500);
            }

            $userType = $em->getRepository("BlzBundle:UserType")->findOneById(4);

            if(empty($userType)){
                throw new \Exception("Tipo de usuário não encontrado", 404);
            }

            $user = $em->createQueryBuilder()
            ->select("u")
            ->from("BlzBundle:User", "u")
            ->join("BlzBundle:UserData", "d", "WITH", "u.userData = d")
            ->where("u.type = :type")
            ->andWhere("u.email = :email")
            ->andWhere("d.social = true")
            ->setParameter("type", $userType)
            ->setParameter("email", $email);
            $user = $user->getQuery()->getOneOrNullResult();

            if(empty($user)){
                $data = new UserData;
                $data->setPhoto($photo);
                $data->setSocial(true);
    
                $em->persist($data);
    
                $user = new User;
                $user->setName($name);
                $user->setEmail($email);
                $user->setType($userType);
                $user->setUserData($data);

                $em->persist($user);
            }
            else{
                $user->setEmail($email);
                $user->setName($name);
                $user->getUserData()->setPhoto($photo);
            }

            $em->flush();

            $em->getConnection()->commit();
            return new Response($this->getProfile($user));
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            $em->getConnection()->rollBack();
            return new Response($message, $status);
        }
    }

    /**
     * @Route("/register-complete/")
     * @Method("POST")
     */
    public function registerCompleteAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");
        $phone = $request->request->get("phone") ? $request->request->get("phone") : "1111111111";
        $celphone = $request->request->get("celphone") ? $request->request->get("celphone") : "11111111111";
        $rg = $request->request->get("rg") ? $request->request->get("rg") : "111111111";
        $cpf = $request->request->get("cpf") ? $request->request->get("cpf") : "11111111111";

        try{
            if(empty($celphone) || empty($rg) || empty($cpf)){
                throw new \Exception("Dados incompletos", 500);
            }

            $user = $em->getRepository("BlzBundle:User")->findOneById($id);

            if(empty($user)){
                throw new \Exception("Usuário não encontrado", 404);
            }

            $data = $user->getUserData();
            $data->setPhone(preg_replace("/[^0-9]+/", "", $phone));
            $data->setCelphone(preg_replace("/[^0-9]+/", "", $celphone));
            $data->setRg(preg_replace("/[^0-9]+/", "", $rg));
            $data->setCpf(preg_replace("/[^0-9]+/", "", $cpf));

            $em->flush();

            return new Response($this->getProfile($user));
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            return new Response($message, $status);
        }
    }

    /**
     * @Route("/edit/")
     * @Method("POST")
     */
    public function editAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $id = $request->request->get("id");
        $name = $request->request->get("name");
        $phone = $request->request->get("phone") ? $request->request->get("phone") : "1111111111";
        $celphone = $request->request->get("celphone") ? $request->request->get("celphone") : "11111111111";
        $rg = $request->request->get("rg") ? $request->request->get("rg") : "111111111";
        $cpf = $request->request->get("cpf") ? $request->request->get("cpf") : "11111111111";
        $password = $request->request->get("password");
        $photo = $request->request->get("photo");

        try{
            if(empty($name) || empty($celphone) || empty($rg) || empty($cpf)){
                throw new \Exception("Dados incompletos", 500);
            }

            $user = $em->getRepository("BlzBundle:User")->findOneById($id);

            if(empty($user)){
                throw new \Exception("Usuário não encontrado", 404);
            }

            $data = $user->getUserData();
            $data->setPhone(preg_replace("/[^0-9]+/", "", $phone));
            $data->setCelphone(preg_replace("/[^0-9]+/", "", $celphone));
            $data->setRg(preg_replace("/[^0-9]+/", "", $rg));
            $data->setCpf(preg_replace("/[^0-9]+/", "", $cpf));

            if(empty($user->getUserData()->getSocial())){
                $user->setName($name);
            
                if(!empty($password)){
                    $user->setPassword(md5($password));
                }

                if(!empty($photo)){
                    $filename = md5(uniqid()) . ".png";
                    $photo = base64_decode(str_replace(" ", "+", str_replace("data:image/png;base64,", "", $photo)));
        
                    if(file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/upload/user/" . $filename, $photo)){
                        if(is_file($_SERVER["DOCUMENT_ROOT"] . "/upload/user/" . $data->getPhoto())){
                            unlink($_SERVER["DOCUMENT_ROOT"] . "/upload/user/" . $data->getPhoto());
                        }
                        $data->setPhoto($filename);
                    }
                    else{
                        throw new \Exception("Ocorreu um erro no upload da foto", 500);
                    }
                }
            }

            $em->flush();

            $em->getConnection()->commit();
            return new Response($this->getProfile($user));
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            $em->getConnection()->rollBack();
            return new Response($message, $status);
        }
    }

    /**
     * @Route("/password-recovery/")
     * @Method("POST")
     */
    public function passwordRecoveryAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $email = $request->request->get("email");

        try{
            if(empty($email)){
                throw new \Exception("Dados incompletos", 500);
            }

            $user = $em->getRepository("BlzBundle:User")->findOneByEmail($email);

            if(empty($user)){
                throw new \Exception("Usuário não encontrado", 404);
            }

            if(empty($user->getActive())){
                throw new \Exception("Usuário inativo", 500);
            }

            $deleteOldRequests = $em->getConnection()->prepare("DELETE FROM password_recovery WHERE user = ?");
            $deleteOldRequests->execute(array($user->getId()));

            $hash = md5(uniqid());

            $passwordRec = new PasswordRecovery;
            $passwordRec->setHash($hash);
            $passwordRec->setUser($user);

            $em->persist($passwordRec);
            $em->flush();

            $em->getConnection()->commit();
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            $em->getConnection()->rollBack();
            return new Response($message, $status);
        }

        try{
            $mail = new PHPMailer(true);

            $mail->setFrom("noreply@aplicativobeleza.com", "Beleza.com");
            $mail->addAddress($email, $user->getName());
            $mail->addReplyTo("contato@aplicativobeleza.com", "Beleza.com");

            $mail->isHTML(true);
            $mail->CharSet = "UTF-8";
            $mail->Subject = "Recuperação de conta | Beleza.com";
            $mail->Body    = "<!DOCTYPE html>
                            <html>
                                <head>
                                    <title>Recuperação de conta | Beleza.com</title>
                                </head>
                                <body style='text-align: center'>
                                    <p>Para continuar a recuperação da conta acesse: </p>
                                    <p><a href='http://" . $_SERVER["SERVER_NAME"] . "/recuperar-senha/{$hash}/'>http://" . $_SERVER["SERVER_NAME"] . "/recuperar-senha/{$hash}/</a></p>
                                </body>
                            </html>";

            $mail->AltBody = "Recuperação de conta | Beleza.com";
        
            $mail->send();

            return new Response(json_encode("success"));
        }
        catch(\Exception $e){
            return new Response("Ocorreu um erro ao enviar o email", 500);
        }
    }

    /**
     * @Route("/change-password/")
     * @Method("POST")
     */
    public function changePasswordAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $hash = $request->request->get("hash");
        $password = $request->request->get("password");

        try{
            if(empty($hash) || empty($password)){
                throw new \Exception("Dados incompletos", 500);
            }

            $passwordRec = $em->getRepository("BlzBundle:PasswordRecovery")->findOneByHash($hash);

            if(empty($passwordRec)){
                throw new \Exception("Solicitação não encontrada", 404);
            }

            $user = $passwordRec->getUser();
            $user->setPassword(md5($password));

            $em->remove($passwordRec);
            $em->flush();

            $em->getConnection()->commit();
            return new Response(json_encode("success")); 
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            $em->getConnection()->rollBack();
            return new Response($message, $status);
        }
    }

    /**
     * @Route("/category-specialty/")
     * @Method("POST")
     */
    public function categorySpecialtyAction()
    {
        $em = $this->getDoctrine()->getManager();

        $category = $em->getConnection()->prepare("SELECT * FROM category WHERE active = true ORDER BY priority ASC");
        $category->execute();
        $category = $category->fetchAll();

        for($i = 0; $i < count($category); $i = $i + 1){
            $specialty = $em->getConnection()->prepare("SELECT * FROM specialty WHERE active = true AND category = ? ORDER BY priority ASC");
            $specialty->execute(array($category[$i]["id"]));
            $specialty = $specialty->fetchAll();

            $category[$i]["specialty"] = $specialty;
        }

        return new Response(json_encode($category));
    }

    /**
     * @Route("/category-specialty-provider/")
     * @Method("POST")
     */
    public function categorySpecialtyproviderAction(Request $request)
    {
        $request->request->replace($this->getContent($request));
        
        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");

        $category = $em->getConnection()->prepare("SELECT c.id, c.name FROM category c
        INNER JOIN specialty s ON s.category = c.id
        INNER JOIN provider_service ps ON ps.specialty = s.id
        WHERE c.active = true AND ps.user = ? AND ps.active = true AND ps.deleted = false GROUP BY c.id ORDER BY c.priority ASC");
        $category->execute(array($id));
        $category = $category->fetchAll();

        for($i = 0; $i < count($category); $i = $i + 1){
            $specialty = $em->getConnection()->prepare("SELECT s.id, s.name FROM specialty s
            INNER JOIN provider_service ps ON ps.specialty = s.id
            WHERE s.active = true AND s.category = ? AND ps.user = ? AND ps.active = true AND ps.deleted = false GROUP BY s.id ORDER BY s.priority ASC");
            $specialty->execute(array($category[$i]["id"], $id));
            $specialty = $specialty->fetchAll();

            $category[$i]["specialty"] = $specialty;
        }

        return new Response(json_encode($category));
    }

    /**
     * @Route("/ad/")
     * @Method("POST")
     */
    public function adAction()
    {
        $em = $this->getDoctrine()->getManager();

        $ad = $em->getConnection()->prepare("SELECT ad.id, ad.name, ad.description, ad.datetime, ad.validity, ad.photo, ad.provider FROM ad 
        INNER JOIN user p ON ad.provider = p.id
        INNER JOIN provider_data d ON p.provider_data = d.id
        WHERE ad.active = true AND p.active = true AND (validity IS NULL OR validity >= CURDATE())
        ORDER BY plan");
        $ad->execute();
        $ad = $ad->fetchAll();

        return new Response(json_encode($ad));
    }

    /**
     * @Route("/providers/")
     * @Method("POST")
     */
    public function providersAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $specialty = $request->request->get("specialty");
        $location = $request->request->get("location") == "geo" ? true : NULL;
        $coordX = $request->request->get("coord_x");
        $coordY = $request->request->get("coord_y");
        $distance = $request->request->get("distance");
        $city = $request->request->get("city");
        $neighborhood = $request->request->get("neighborhood");
        $providerService = $request->request->get("provider_service");
        $homeCare = !empty($request->request->get("home_care")) ? true : NULL;
        $order = $request->request->get("order");

        switch($order){
            case "distance":
                $orderBy = "ORDER BY distance";
            break;
            case "score":
            default:
                $orderBy = "ORDER BY d.points DESC";
        }

        $providers = $em->getConnection()->prepare("SELECT u.id, u.name, d.photo, DISTANCE_BETWEEN(:coord_x1, :coord_y1, d.coord_x, d.coord_y) distance FROM user u
        INNER JOIN provider_data d ON u.provider_data = d.id
        INNER JOIN address a ON d.address = a.id
        LEFT JOIN (SELECT user, specialty FROM provider_service WHERE active = true AND deleted = false GROUP BY user, specialty) s ON s.user = u.id AND s.specialty = :specialty
        LEFT JOIN (SELECT COUNT(id) total, user FROM provider_service WHERE active = true AND deleted = false AND name LIKE CONCAT('%', :provider_service, '%') GROUP BY user) ps ON ps.user = u.id
        LEFT JOIN (SELECT COUNT(id) total, provider FROM provider_payment GROUP BY provider) p ON u.id = p.provider
        LEFT JOIN (SELECT COUNT(d.id) total, d.user FROM available_date d INNER JOIN available_time t ON d.id = t.date LEFT JOIN scheduling s ON s.date = d.date AND s.time = t.time WHERE d.date >= CURDATE() AND (s.id IS NULL OR s.status = 2) AND (d.date != CURDATE() OR t.time > NOW()) GROUP BY d.user) available_time ON u.id = available_time.user
        WHERE type = 3
        AND u.active = true
        AND p.total > 0
        AND available_time.total > 0
        AND (:specialty1 IS NULL OR s.specialty = :specialty2)
        AND (:location IS NOT NULL OR (a.city = :city AND (:neighborhood IS NULL OR a.neighborhood LIKE CONCAT('%', :neighborhood1, '%'))))
        AND (:location1 IS NULL OR :distance >= DISTANCE_BETWEEN(:coord_x, :coord_y, d.coord_x, d.coord_y))
        AND (:provider_service1 IS NULL OR ps.total > 0)
        AND (:home_care1 IS NULL OR d.home_care = :home_care) " . $orderBy);
        $providers->execute(array(
            "specialty" => $specialty,
            "specialty1" => $specialty,
            "specialty2" => $specialty,
            "location" => $location,
            "location1" => $location,
            "city" => $city,
            "neighborhood" => $neighborhood,
            "neighborhood1" => $neighborhood,
            "distance" => $distance,
            "coord_x" => $coordX,
            "coord_y" => $coordY,
            "coord_x1" => $coordX,
            "coord_y1" => $coordY,
            "provider_service" => $providerService,
            "provider_service1" => $providerService,
            "home_care" => $homeCare,
            "home_care1" => $homeCare
        ));
        $providers = $providers->fetchAll();

        $filtredProviders = array();
        for($i = 0; $i < count($providers); $i = $i + 1){
            $specialty = $em->getConnection()->prepare("SELECT DISTINCT sp.name FROM provider_service p INNER JOIN specialty sp ON p.specialty = sp.id WHERE p.user = ? ORDER BY sp.priority ASC");
            $specialty->execute(array($providers[$i]["id"]));
            $specialty = $specialty->fetchAll();

            $specialtyList = array();
            for($j = 0; $j < count($specialty); $j = $j + 1){
                $specialtyList[] = $specialty[$j]["name"];
            }

            $providers[$i]["specialty"] = implode(", ", $specialtyList);
        }

        return new Response(json_encode($providers));
    }

    /**
     * @Route("/provider/")
     * @Method("POST")
     */
    public function providerAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");

        $provider = $em->getConnection()->prepare("SELECT u.id, u.name, d.description, d.photo, d.home_care, a.cep, a.street, a.neighborhood, a.number, c.name city, s.uf state FROM user u
        INNER JOIN provider_data d ON u.provider_data = d.id
        INNER JOIN address a ON d.address = a.id
        INNER JOIN city c ON a.city = c.id
        INNER JOIN state s ON c.state = s.id
        WHERE u.type = 3 AND u.active = true AND u.id = ?");
        $provider->execute(array($id));
        $provider = $provider->fetch();

        $availableDate = $em->getConnection()->prepare("SELECT d.id, DATE_FORMAT(d.date, '%d/%m/%Y') date FROM available_date d
        LEFT JOIN (SELECT d.id, COUNT(d.id) total FROM available_date d INNER JOIN available_time t ON d.id = t.date LEFT JOIN scheduling s ON s.date = d.date AND s.time = t.time WHERE d.date >= CURDATE() AND (s.id IS NULL OR s.status = 2) AND (d.date != CURDATE() OR t.time > NOW()) GROUP BY d.id) available_time ON d.id = available_time.id
        WHERE d.date >= CURDATE() AND d.user = ? AND available_time.total > 0 
        ORDER BY d.date");
        $availableDate->execute(array($id));

        $provider["dates"] = $availableDate->fetchAll();

        $services = $em->getConnection()->prepare("SELECT p.id, p.name, p.description, p.specialty FROM provider_service p INNER JOIN specialty s ON p.specialty = s.id INNER JOIN category c ON s.category = c.id WHERE p.active = true AND p.deleted = false AND s.active = true AND c.active = true AND p.user = ? ORDER BY p.priority ASC");
        $services->execute(array($id));

        $provider["services"] = $services->fetchAll();

        for($i = 0; $i < count($provider["services"]); $i = $i + 1){
            $prices = $em->getConnection()->prepare("SELECT pp.payment_method, REPLACE(ROUND(psp.price, 2), '.', ',') price FROM provider_service_price psp
            INNER JOIN provider_payment pp ON psp.provider_payment = pp.id
            INNER JOIN provider_service ps ON psp.provider_service = ps.id
            WHERE ps.id = ?");
            $prices->execute(array($provider["services"][$i]["id"]));
            
            $provider["services"][$i]["prices"] = $prices->fetchAll();
        }

        $payments = $em->getConnection()->prepare("SELECT pm.id, name, photo FROM provider_payment p INNER JOIN payment_method pm ON p.payment_method = pm.id WHERE p.provider = ? AND pm.active = true");
        $payments->execute(array($id));

        $provider["payments"] = $payments->fetchAll();

        return new Response(json_encode($provider));
    }

    /**
     * @Route("/provider/schedule/date/")
     * @Method("POST")
     */
    public function providerDateAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");

        $availableDate = $em->getConnection()->prepare("SELECT id, date FROM available_date WHERE id = ?");
        $availableDate->execute(array($id));
        $availableDate = $availableDate->fetch();

        $now = new \DateTime;
        $minHour = "";
        if($availableDate["date"] == $now->format("Y-m-d")){
            $minHour = "AND t.time > '" . $now->format("H:i") . "'";
        }

        $availableTime = $em->getConnection()->prepare("SELECT t.id, TIME_FORMAT(t.time, '%H:%i') time FROM available_time t
        INNER JOIN available_date d ON t.date = d.id
        LEFT JOIN scheduling s ON s.date = d.date AND s.time = t.time
        WHERE t.date = ? AND (s.id IS NULL OR s.status = 2) " . $minHour .
        "ORDER BY t.time");
        $availableTime->execute(array($availableDate["id"]));
        $availableTime = $availableTime->fetchAll();

        return new Response(json_encode($availableTime));
    }

    /**
     * @Route("/addresses/")
     * @Method("POST")
     */
    public function addressesAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $user = $request->request->get("id");

        $addresses = $em->getConnection()->prepare("SELECT a.id, a.cep, a.street, a.neighborhood, a.number, c.name city, s.uf state FROM user_address ua
        INNER JOIN address a ON ua.address = a.id
        INNER JOIN city c ON a.city = c.id
        INNER JOIN state s ON c.state = s.id
        WHERE user = ?
        ORDER BY ua.id DESC");
        $addresses->execute(array($user));
        $addresses = $addresses->fetchAll();

        return new Response(json_encode($addresses));
    }

    /**
     * @Route("/address/create/")
     * @Method("POST")
     */
    public function createAddressAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $id = $request->request->get("id");
        $cep = $request->request->get("cep");
        $street = $request->request->get("street");
        $neighborhood = $request->request->get("neighborhood");
        $number = $request->request->get("number");
        $city = $request->request->get("city");

        try{
            if(empty($id) || empty($cep) || empty($street) || empty($neighborhood) || empty($number) || empty($city)){
                throw new \Exception("Dados incompletos", 500);
            }

            $user = $em->getRepository("BlzBundle:User")->findOneById($id);

            if(empty($user)){
                throw new \Exception("Usuário não encontrado", 404);
            }

            $city = $em->getRepository("BlzBundle:City")->findOneById($city);

            if(empty($city)){
                throw new \Exception("Cidade não encontrada", 404);
            }

            $address = new Address;
            $address->setCep(preg_replace("/([^0-9]+)/", "", $cep));
            $address->setStreet($street);
            $address->setNeighborhood($neighborhood);
            $address->setNumber($number);
            $address->setCity($city);

            $em->persist($address);

            $userAddress = new UserAddress;
            $userAddress->setUser($user);
            $userAddress->setAddress($address);

            $em->persist($userAddress);
            $em->flush();

            $em->getConnection()->commit();
            return new Response(json_encode(array(
                "id" => $address->getId(),
                "cep" => $address->getCep(),
                "street" => $address->getStreet(),
                "neighborhood" => $address->getNeighborhood(),
                "number" => $address->getNumber(),
                "city" => $address->getCity()->getName(),
                "state" => $address->getCity()->getState()->getUf()
            )));
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            $em->getConnection()->rollBack();
            return new Response($message, $status);
        }
    }

    /**
     * @Route("/address/delete/")
     * @Method("POST")
     */
    public function deleteAddressAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $id = $request->request->get("id");

        try{
            if(empty($id)){
                throw new \Exception("Dados incompletos", 500);
            }

            $address = $em->getRepository("BlzBundle:Address")->findOneById($id);
            $userAddress = $em->getRepository("BlzBundle:UserAddress")->findOneByAddress($address);

            if(empty($address) || empty($userAddress)){
                throw new \Exception("Endereço não encontrado", 404);
            }

            $em->remove($userAddress);
            $em->remove($address);

            $em->flush();

            $em->getConnection()->commit();
            return new Response(json_encode("success"));
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            $em->getConnection()->rollBack();
            return new Response($message, $status);
        }
    }

    /**
     * @Route("/scheduling/")
     * @Method("POST")
     */
    public function schedulingAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $date = $request->request->get("date");
        $time = $request->request->get("time");
        $homeCare = !empty($request->request->get("home_care")) ? true : false;
        $address = $request->request->get("address");
        $paymentMethod = $request->request->get("payment");
        $services = $request->request->get("services");
        $user = $request->request->get("user");
        $provider = $request->request->get("provider");

        try{
            if(empty($date) || empty($time) || empty($paymentMethod) || empty($user) || empty($provider) || count($services) == 0){
                throw new \Exception("Dados incompletos", 500);
            }

            $user = $em->getRepository("BlzBundle:User")->findOneBy(array(
                "id" => $user,
                "active" => true
            ));

            if(empty($user)){
                throw new \Exception("Usuário não encontrado", 404);
            }

            $provider = $em->getRepository("BlzBundle:User")->findOneBy(array(
                "id" => $provider,
                "active" => true
            ));

            if(empty($provider)){
                throw new \Exception("Prestado de serviços não encontrado", 404);
            }

            $date = $em->getRepository("BlzBundle:AvailableDate")->findOneBy(array(
                "id" => $date,
                "user" => $provider
            ));

            if(empty($date)){
                throw new \Exception("Data não disponível", 500);
            }

            $time = $em->getRepository("BlzBundle:AvailableTime")->findOneBy(array(
                "id" => $time,
                "date" => $date
            ));

            if(empty($time)){
                throw new \Exception("Horário não disponível", 500);
            }

            $paymentMethod = $em->getRepository("BlzBundle:PaymentMethod")->findOneById($paymentMethod);

            if(empty($paymentMethod)){
                throw new \Exception("Método de pagamento não encontrado", 404);
            }

            $providerPayment = $em->getRepository("BlzBundle:ProviderPayment")->findOneBy(array(
                "provider" => $provider,
                "paymentMethod" => $paymentMethod
            ));

            if(empty($providerPayment)){
                throw new \Exception("Método de pagamento não encontrado", 404);
            }

            $checkScheduling = $em->getRepository("BlzBundle:Scheduling")->findOneBy(array(
                "date" => $date->getDate(),
                "time" => $time->getTime(),
                "provider" => $provider,
                "status" => array(1, 3)
            ));

            if(!empty($checkScheduling)){
                throw new \Exception("O profissional não está mais disponível nesse horário e data", 500);
            }

            $scheduling = new Scheduling;
            $scheduling->setDate($date->getDate());
            $scheduling->setTime($time->getTime());
            $scheduling->setHomeCare($homeCare);
            $scheduling->setUser($user);
            $scheduling->setProvider($provider);
            $scheduling->setPaymentMethod($paymentMethod);
            $scheduling->setStatus($em->getRepository("BlzBundle:SchedulingStatus")->findOneById(1));

            if($homeCare){
                if(empty($address)){
                    throw new \Exception("É necessário um endereço para atendimento a domicilio", 500);
                }

                $address = $em->getRepository("BlzBundle:Address")->findOneById($address);

                if(empty($address)){
                    throw new \Exception("Endereço não encontrado", 404);
                }

                $scheduling->setAddress($address);
            }

            $em->persist($scheduling);

            $servicesList = array();
            $biggerTime = 0;
            for($i = 0; $i < count($services); $i = $i + 1){
                $service = $em->getRepository("BlzBundle:ProviderServicePrice")->findOneBy(array(
                    "providerService" => $services[$i],
                    "providerPayment" => $providerPayment
                ));

                if(empty($service)){
                    throw new \Exception("Serviço não encontrado", 404);
                }

                if($service->getProviderService()->getUser()->getId() != $provider->getId()){
                    throw new \Exception("Serviço indisponível", 403);
                }

                if($biggerTime < $service->getProviderService()->getTime()){
                    $biggerTime = $service->getProviderService()->getTime();
                }

                $servicesList[] = $service->getProviderService()->getName();

                $schedulingService = new SchedulingService;
                $schedulingService->setPrice($service->getPrice());
                $schedulingService->setScheduling($scheduling);
                $schedulingService->setService($service->getProviderService());

                $em->persist($schedulingService);
            }
            $servicesList = implode(", ", $servicesList);

            $schedulingDate = $date;
            $schedulingTime = $time->getTime()->format("H:i");
            $initialTime = 0;
            for(; $initialTime < count($this->times); $initialTime = $initialTime + 1){
                if($schedulingTime == $this->times[$initialTime]){
                    break;
                }
            }

            for($i = 0; $i <= $biggerTime; $i = $i + 1){
                if(empty($this->times[$initialTime])){
                    $initialTime = 0;

                    $schedulingDate = $date = $em->getRepository("BlzBundle:AvailableDate")->findOneBy(array(
                        "date" => $schedulingDate->getDate()->modify("+1 day"),
                        "user" => $provider
                    ));
                }

                if(!empty($schedulingDate)){
                    $checkTime = $em->getRepository("BlzBundle:AvailableTime")->findOneBy(array(
                        "time" => new \DateTime($this->times[$initialTime]),
                        "date" => $schedulingDate
                    ));

                    if(!empty($checkTime)){
                        $em->remove($checkTime);
                    }
                }

                $initialTime = $initialTime + 1;
            }

            $em->flush();

            $em->getConnection()->commit();
            return new Response(json_encode(array(
                "id" => $scheduling->getId(),
                "name" => $scheduling->getUser()->getName(),
                "services" => $servicesList,
                "date" => $scheduling->getDate()->format("d/m/Y"),
                "time" => $scheduling->getTime()->format("H:i"),
                "push" => $scheduling->getProvider()->getProviderData()->getPush(),
                "push_web" => $scheduling->getProvider()->getProviderData()->getPushWeb()
            )));
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            $em->getConnection()->rollBack();
            return new Response($message, $status);
        }
    }

    /**
     * @Route("/scheduling-manual/")
     * @Method("POST")
     */
    public function schedulingManualAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $notes = $request->request->get("notes");
        $date = $request->request->get("date");
        $time = $request->request->get("time");
        $address = $request->request->get("address");
        $paymentMethod = $request->request->get("payment");
        $services = $request->request->get("services");
        $provider = $request->request->get("provider");

        try{
            if(empty($date) || empty($time) || empty($paymentMethod) || empty($provider) || count($services) == 0){
                throw new \Exception("Dados incompletos", 500);
            }

            $user = $em->getRepository("BlzBundle:User")->findOneBy(array(
                "email" => "agendamentos@aplicativobeleza.com",
                "active" => true
            ));

            if(empty($user)){
                throw new \Exception("Usuário não encontrado", 404);
            }

            $provider = $em->getRepository("BlzBundle:User")->findOneBy(array(
                "id" => $provider,
                "active" => true
            ));

            if(empty($provider)){
                throw new \Exception("Prestado de serviços não encontrado", 404);
            }

            $date = $em->getRepository("BlzBundle:AvailableDate")->findOneBy(array(
                "id" => $date,
                "user" => $provider
            ));

            if(empty($date)){
                throw new \Exception("Data não disponível", 500);
            }

            $time = $em->getRepository("BlzBundle:AvailableTime")->findOneBy(array(
                "id" => $time,
                "date" => $date
            ));

            if(empty($time)){
                throw new \Exception("Horário não disponível", 500);
            }

            $paymentMethod = $em->getRepository("BlzBundle:PaymentMethod")->findOneById($paymentMethod);

            if(empty($paymentMethod)){
                throw new \Exception("Método de pagamento não encontrado", 404);
            }

            $providerPayment = $em->getRepository("BlzBundle:ProviderPayment")->findOneBy(array(
                "provider" => $provider,
                "paymentMethod" => $paymentMethod
            ));

            if(empty($providerPayment)){
                throw new \Exception("Método de pagamento não encontrado", 404);
            }

            $checkScheduling = $em->getRepository("BlzBundle:Scheduling")->findOneBy(array(
                "date" => $date->getDate(),
                "time" => $time->getTime(),
                "provider" => $provider,
                "status" => array(1, 3)
            ));

            if(!empty($checkScheduling)){
                throw new \Exception("O profissional não está mais disponível nesse horário e data", 500);
            }

            $scheduling = new Scheduling;
            $scheduling->setDate($date->getDate());
            $scheduling->setTime($time->getTime());
            $scheduling->setHomeCare(false);
            $scheduling->setNotes($notes);
            $scheduling->setUser($user);
            $scheduling->setProvider($provider);
            $scheduling->setPaymentMethod($paymentMethod);
            $scheduling->setStatus($em->getRepository("BlzBundle:SchedulingStatus")->findOneById(1));

            $em->persist($scheduling);

            $servicesList = array();
            $biggerTime = 0;
            for($i = 0; $i < count($services); $i = $i + 1){
                $service = $em->getRepository("BlzBundle:ProviderServicePrice")->findOneBy(array(
                    "providerService" => $services[$i],
                    "providerPayment" => $providerPayment
                ));

                if(empty($service)){
                    throw new \Exception("Serviço não encontrado", 404);
                }

                if($service->getProviderService()->getUser()->getId() != $provider->getId()){
                    throw new \Exception("Serviço indisponível", 403);
                }

                if($biggerTime < $service->getProviderService()->getTime()){
                    $biggerTime = $service->getProviderService()->getTime();
                }

                $servicesList[] = $service->getProviderService()->getName();

                $schedulingService = new SchedulingService;
                $schedulingService->setPrice($service->getPrice());
                $schedulingService->setScheduling($scheduling);
                $schedulingService->setService($service->getProviderService());

                $em->persist($schedulingService);
            }
            $servicesList = implode(", ", $servicesList);

            $schedulingDate = $date;
            $schedulingTime = $time->getTime()->format("H:i");
            $initialTime = 0;
            for(; $initialTime < count($this->times); $initialTime = $initialTime + 1){
                if($schedulingTime == $this->times[$initialTime]){
                    break;
                }
            }

            for($i = 0; $i <= $biggerTime; $i = $i + 1){
                if(empty($this->times[$initialTime])){
                    $initialTime = 0;

                    $schedulingDate = $date = $em->getRepository("BlzBundle:AvailableDate")->findOneBy(array(
                        "date" => $schedulingDate->getDate()->modify("+1 day"),
                        "user" => $provider
                    ));
                }

                if(!empty($schedulingDate)){
                    $checkTime = $em->getRepository("BlzBundle:AvailableTime")->findOneBy(array(
                        "time" => new \DateTime($this->times[$initialTime]),
                        "date" => $schedulingDate
                    ));

                    if(!empty($checkTime)){
                        $em->remove($checkTime);
                    }
                }

                $initialTime = $initialTime + 1;
            }

            $em->flush();

            $em->getConnection()->commit();
            return new Response(json_encode(array(
                "id" => $scheduling->getId()
            )));
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            $em->getConnection()->rollBack();
            return new Response($message, $status);
        }
    }

    /**
     * @Route("/scheduling/edit/")
     * @Method("POST")
     */
    public function schedulingEditAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $id = $request->request->get("id");
        $date = $request->request->get("date");
        $time = $request->request->get("time");
        $homeCare = !empty($request->request->get("home_care")) ? true : false;
        $address = $request->request->get("address");
        $paymentMethod = $request->request->get("payment");
        $services = $request->request->get("services");

        try{
            if(empty($id) || empty($date) || empty($paymentMethod) || count($services) == 0){
                throw new \Exception("Dados incompletos", 500);
            }

            $scheduling = $em->getRepository("BlzBundle:Scheduling")->findOneById($id);

            if(empty($scheduling)){
                throw new \Exception("Agendamento não encontrado", 404);
            }

            if($scheduling->getStatus()->getId() != 1){
                throw new \Exception("Somente agendamentos aguardando podem ser editados", 500);
            }

            $date = $em->getRepository("BlzBundle:AvailableDate")->findOneBy(array(
                "id" => $date,
                "user" => $scheduling->getProvider()
            ));

            if(empty($date)){
                throw new \Exception("Data não disponível", 500);
            }

            $paymentMethod = $em->getRepository("BlzBundle:PaymentMethod")->findOneById($paymentMethod);

            if(empty($paymentMethod)){
                throw new \Exception("Método de pagamento não encontrado", 404);
            }

            $providerPayment = $em->getRepository("BlzBundle:ProviderPayment")->findOneBy(array(
                "provider" => $scheduling->getProvider(),
                "paymentMethod" => $paymentMethod
            ));

            if(empty($providerPayment)){
                throw new \Exception("Método de pagamento não encontrado", 404);
            }

            if(!empty($time)){
                $time = $em->getRepository("BlzBundle:AvailableTime")->findOneBy(array(
                    "id" => $time,
                    "date" => $date
                ));

                $scheduling->setTime($time->getTime());

                $checkScheduling = $em->getRepository("BlzBundle:Scheduling")->findOneBy(array(
                    "date" => $date->getDate(),
                    "time" => $time->getTime(),
                    "provider" => $scheduling->getProvider(),
                    "status" => array(1, 3)
                ));
    
                if(!empty($checkScheduling) && $checkScheduling->getId() != $scheduling->getId()){
                    throw new \Exception("O profissional não está mais disponível nesse horário e data", 500);
                }
            }

            $scheduling->setDate($date->getDate());
            $scheduling->setHomeCare($homeCare);
            $scheduling->setPaymentMethod($paymentMethod);

            if($homeCare){
                if(empty($address)){
                    throw new \Exception("É necessário um endereço para atendimento a domicilio", 500);
                }

                $address = $em->getRepository("BlzBundle:Address")->findOneById($address);

                if(empty($address)){
                    throw new \Exception("Endereço não encontrado", 404);
                }

                $scheduling->setAddress($address);
            }
            else{
                $scheduling->setAddress(NULL);
            }

            $resetServices = $em->getConnection()->prepare("DELETE FROM scheduling_service WHERE scheduling = ?");
            $resetServices->execute(array($scheduling->getId()));

            $servicesList = array();
            $biggerTime = 0;
            for($i = 0; $i < count($services); $i = $i + 1){
                $service = $em->getRepository("BlzBundle:ProviderServicePrice")->findOneBy(array(
                    "providerService" => $services[$i],
                    "providerPayment" => $providerPayment
                ));

                if(empty($service)){
                    throw new \Exception("Serviço não encontrado", 404);
                }

                if($service->getProviderService()->getUser()->getId() != $provider->getId()){
                    throw new \Exception("Serviço indisponível", 403);
                }

                if($biggerTime < $service->getProviderService()->getTime()){
                    $biggerTime = $service->getProviderService()->getTime();
                }

                $servicesList[] = $service->getProviderService()->getName();

                $schedulingService = new SchedulingService;
                $schedulingService->setPrice($service->getPrice());
                $schedulingService->setScheduling($scheduling);
                $schedulingService->setService($service->getProviderService());

                $em->persist($schedulingService);
            }
            $servicesList = implode(", ", $servicesList);

            $schedulingDate = $date;
            $schedulingTime = $time->getTime()->format("H:i");
            $initialTime = 0;
            for(; $initialTime < count($this->times); $initialTime = $initialTime + 1){
                if($schedulingTime == $this->times[$initialTime]){
                    break;
                }
            }

            for($i = 0; $i <= $biggerTime; $i = $i + 1){
                if(empty($this->times[$initialTime])){
                    $initialTime = 0;

                    $schedulingDate = $date = $em->getRepository("BlzBundle:AvailableDate")->findOneBy(array(
                        "date" => $schedulingDate->getDate()->modify("+1 day"),
                        "user" => $provider
                    ));
                }

                if(!empty($schedulingDate)){
                    $checkTime = $em->getRepository("BlzBundle:AvailableTime")->findOneBy(array(
                        "time" => new \DateTime($this->times[$initialTime]),
                        "date" => $schedulingDate
                    ));

                    if(!empty($checkTime)){
                        $em->remove($checkTime);
                    }
                }

                $initialTime = $initialTime + 1;
            }

            $em->flush();

            $em->getConnection()->commit();
            return new Response(json_encode(array(
                "id" => $scheduling->getId(),
                "name" => $scheduling->getUser()->getName(),
                "services" => $servicesList,
                "date" => $scheduling->getDate()->format("d/m/Y"),
                "time" => $scheduling->getTime()->format("H:i"),
                "push" => $scheduling->getProvider()->getProviderData()->getPush(),
                "push_web" => $scheduling->getProvider()->getProviderData()->getPushWeb()
            )));
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            $em->getConnection()->rollBack();
            return new Response($message, $status);
        }
    }

    /**
     * @Route("/scheduling/list/")
     * @Method("POST")
     */
    public function listSchedulingAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $user = $request->request->get("user");
        $status = $request->request->get("status");

        $scheduling = $em->getConnection()->prepare("SELECT s.id, p.id provider, p.name, d.photo, DATE_FORMAT(date, '%d/%m/%Y') date, TIME_FORMAT(time, '%H:%i') time, ROUND(s.rating, 2) rating FROM scheduling s
        INNER JOIN user p ON s.provider = p.id
        INNER JOIN provider_data d ON p.provider_data = d.id
        WHERE s.user = :user AND s.status = :status
        ORDER BY s.id DESC");
        $scheduling->execute(array(
            "user" => $user,
            "status" => $status
        ));
        $scheduling = $scheduling->fetchAll();

        for($i = 0; $i < count($scheduling); $i = $i + 1){
            $services = $em->getConnection()->prepare("SELECT name FROM scheduling_service s
            INNER JOIN provider_service p ON s.service = p.id
            WHERE scheduling = ? ORDER BY p.priority ASC");
            $services->execute(array($scheduling[$i]["id"]));
            $services = $services->fetchAll();

            $servicesList = array();
            for($j = 0; $j < count($services); $j = $j + 1){
                $servicesList[] = $services[$j]["name"];
            }
            
            $scheduling[$i]["services"] =  implode(", ", $servicesList);
        }

        return new Response(json_encode($scheduling));
    }

    /**
     * @Route("/scheduling/cancel/")
     * @Method("POST")
     */
    public function schedulingCancelAction(Request $request)
    {
        $request->request->replace($this->getContent($request));
        
        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");
        $user = $request->request->get("user");

        try{
            if(empty($id) || empty($user)){
                throw new \Exception("Dados incompletos", 500);
            }

            $user = $em->getRepository("BlzBundle:User")->findOneById($user);

            if(empty($user)){
                throw new \Exception("Usuário não encontrado", 404);
            }

            $scheduling = $em->getRepository("BlzBundle:Scheduling")->findOneBy(array(
                "id" => $id,
                "user" => $user
            ));

            if(empty($scheduling) || $scheduling->getStatus()->getId() != 1){
                throw new \Exception("Agendamento não encontrado", 404);
            }

            $scheduling->setStatus($em->getRepository("BlzBundle:SchedulingStatus")->findOneById(2));

            $em->flush();

            return new Response(json_encode(array(
                "id" => $scheduling->getId(),
                "user" => $scheduling->getUser()->getName(),
                "provider" => $scheduling->getProvider()->getName(),
                "date" => $scheduling->getDate()->format("d/m/Y"),
                "time" => $scheduling->getTime()->format("H:i"),
                "home_care" => !empty($scheduling->getHomeCare()) ? "Sim" : "Não",
                "payment_method" => $scheduling->getPaymentMethod()->getName(),
                "push" => $scheduling->getProvider()->getProviderData()->getPush(),
                "push_web" => $scheduling->getProvider()->getProviderData()->getPushWeb()
            )));
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            return new Response($message, $status);
        }
    }

    /**
     * @Route("/scheduling/details/")
     * @Method("POST")
     */
    public function schedulingDetailsAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");

        $scheduling = $em->getConnection()->prepare("SELECT s.id, s.home_care, ROUND(s.rating, 2) rating, p.name provider, p.id provider_id, d.photo, d.description, st.name status, st.id status_id, DATE_FORMAT(s.date, '%d/%m/%Y') date, TIME_FORMAT(s.time, '%H:%i') time, DATE_FORMAT(s.datetime, '%d/%m/%Y %H:%i') datetime, pm.name payment_method, pm.id payment_method_id, a.id address_id, a.cep, a.street, a.neighborhood, a.number, c.name city, sta.uf state, ap.cep cep_provider, ap.street street_provider, ap.neighborhood neighborhood_provider, ap.number number_provider, cp.name city_provider, stap.uf state_provider FROM scheduling s
        INNER JOIN user p ON s.provider = p.id
        INNER JOIN provider_data d ON p.provider_data = d.id
        INNER JOIN scheduling_status st ON s.status = st.id
        INNER JOIN payment_method pm ON s.payment_method = pm.id
        LEFT JOIN address a ON s.address = a.id
        LEFT JOIN city c ON a.city = c.id
        LEFT JOIN state sta ON c.state = sta.id
        LEFT JOIN address ap ON d.address = ap.id
        LEFT JOIN city cp ON ap.city = cp.id
        LEFT JOIN state stap ON cp.state = stap.id
        WHERE s.id = ?");
        $scheduling->execute(array($id));
        $scheduling = $scheduling->fetch();

        $services = $em->getConnection()->prepare("SELECT s.service id, name, description, REPLACE(ROUND(s.price, 2), '.', ',') price FROM scheduling_service s
        INNER JOIN provider_service p ON s.service = p.id
        WHERE scheduling = ? ORDER BY p.priority ASC");
        $services->execute(array($id));
        
        $scheduling["services"] = $services->fetchAll();

        $amount = $em->getConnection()->prepare("SELECT REPLACE(ROUND(SUM(s.price), 2), '.', ',') amount FROM scheduling_service s WHERE scheduling = ?");
        $amount->execute(array($id));

        $scheduling["amount"] = $amount->fetch()["amount"];

        return new Response(json_encode($scheduling));
    }

    /**
     * @Route("/push/")
     * @Method("POST")
     */
    public function setPushAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $user = $request->request->get("user");
        $push = $request->request->get("push");

        try{
            if(empty($user) || empty($push)){
                throw new \Exception("Dados incompletos", 500);
            }

            $user = $em->getRepository("BlzBundle:User")->findOneById($user);

            if(empty($user)){
                throw new \Exception("Usuário não encontrado", 404);
            }

            $user->getUserData()->setPush($push);

            $em->flush();

            return new Response(json_encode("success"));
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            return new Response($message, $status);
        }
    }

    /**
     * @Route("/chat-room/")
     * @Method("POST")
     */
    public function chatRoomAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $user = $request->request->get("user");
        $provider = $request->request->get("provider");

        try{
            if(empty($user) || empty($provider)){
                throw new \Exception("Dados incompletos");
            }

            $chatRoom = $em->getConnection()->prepare("SELECT c.id, p.id provider, p.name, photo, push, push_web FROM chat_room c
            INNER JOIN user p ON c.provider = p.id
            INNER JOIN provider_data d ON p.provider_data = d.id
            WHERE user = :user AND provider = :provider");
            $chatRoom->execute(array(
                "user" => $user,
                "provider" => $provider
            ));
            $chatRoom = $chatRoom->fetch();

            if(empty($chatRoom)){
                $user = $em->getRepository("BlzBundle:User")->findOneBy(array(
                    "id" => $user,
                    "active" => true
                ));
    
                if(empty($user)){
                    throw new \Exception("Usuário não encontrado", 404);
                }
    
                $provider = $em->getRepository("BlzBundle:User")->findOneBy(array(
                    "id" => $provider,
                    "active" => true
                ));

                $chatRoom = new ChatRoom;
                $chatRoom->setUser($user);
                $chatRoom->setProvider($provider);
                
                $em->persist($chatRoom);
                $em->flush();

                $chatId = $chatRoom->getId();
                $providerId = $provider->getId();
                $name = $provider->getName();
                $photo = $provider->getProviderData()->getPhoto();
                $push = $provider->getProviderData()->getPush();
                $pushWeb = $provider->getProviderData()->getPushWeb();
                $chat = array();
            }
            else{
                $chatId = $chatRoom["id"];
                $providerId = $chatRoom["provider"];
                $name = $chatRoom["name"];
                $photo = $chatRoom["photo"];
                $push = $chatRoom["push"];
                $pushWeb = $chatRoom["push_web"];
                $chat = $em->getConnection()->prepare("SELECT c.message, c.from_user, c.to_user, DATE_FORMAT(c.datetime, '%d/%m/%Y %H:%i') datetime FROM chat c WHERE chat_room = ?");
                $chat->execute(array($chatId));
                $chat = $chat->fetchAll();
            }

            return new Response(json_encode(array(
                "chat_room" => $chatId,
                "chat" => $chat,
                "provider" => $providerId,
                "name" => $name,
                "photo" => $photo,
                "push" => $push,
                "push_web" => $pushWeb
            )));
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            return new Response($message, $status);
        }
    }

    /**
     * @Route("/chat/add/")
     * @Method("POST")
     */
    public function chatAddAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $chatRoom = $request->request->get("chat_room");
        $message = $request->request->get("message");
        $from = $request->request->get("from_user");
        $to = $request->request->get("to_user");

        try{
            if(empty($chatRoom) || empty($message) || empty($from) || empty($to)){
                throw new \Exception("Dados incompletos", 500);
            }
            
            $chatRoom = $em->getRepository("BlzBundle:ChatRoom")->findOneBy(array(
                "id" => $chatRoom,
                "active" => true
            ));

            if(empty($chatRoom)){
                throw new \Exception("Conversa não encontrada", 404);
            }

            $from = $em->getRepository("BlzBundle:User")->findOneBy(array(
                "id" => $from,
                "active" => true
            ));

            if(empty($from)){
                throw new \Exception("Usuário não encontrado", 404);
            }

            $to = $em->getRepository("BlzBundle:User")->findOneBy(array(
                "id" => $to,
                "active" => true
            ));

            if(empty($to)){
                throw new \Exception("Prestador de serviço não encontrado", 404);
            }

            $chat = new Chat;
            $chat->setMessage($message);
            $chat->setFromUser($from);
            $chat->setToUser($to);
            $chat->setChatRoom($chatRoom);

            $em->persist($chat);

            $chatRoom->setLastMessage($message);
            $chatRoom->setLastDatetime(new \DateTime);

            $em->flush();

            return new Response(json_encode("success"));
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            return new Response($message, $status);
        }
    }

    /**
     * @Route("/chat/list/")
     * @Method("POST")
     */
    public function chatListAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $user = $request->request->get("user");

        $chat = $em->getConnection()->prepare("SELECT p.id provider, p.name, d.photo, last_message, DATE_FORMAT(last_datetime, '%d/%m/%Y %H:%i') last_datetime FROM chat_room c
        INNER JOIN user p ON c.provider = p.id
        INNER JOIN provider_data d ON p.provider_data = d.id
        WHERE p.active = true AND c.active = true AND last_message IS NOT NULL AND last_datetime IS NOT NULL AND user = ?");
        $chat->execute(array($user));
        $chat = $chat->fetchAll();

        return new Response(json_encode($chat));
    }

    /**
     * @Route("/points/")
     * @Method("POST")
     */
    public function pointsAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $user = $request->request->get("user");

        $points = $em->getConnection()->prepare("SELECT p.points, pr.name provider, pd.photo, s.id scheduling FROM user_point p
        LEFT JOIN scheduling s ON p.scheduling = s.id
        LEFT JOIN user pr ON s.provider = pr.id
        LEFT JOIN provider_data pd ON pr.provider_data = pd.id
        WHERE p.user = ?
        AND s.id IS NOT NULL");
        $points->execute(array($user));
        $points = $points->fetchAll();

        for($i = 0; $i < count($points); $i = $i + 1){
            $services = $em->getConnection()->prepare("SELECT name FROM scheduling_service s
            INNER JOIN provider_service p ON s.service = p.id
            WHERE scheduling = ? ORDER BY p.priority ASC");
            $services->execute(array($points[$i]["scheduling"]));
            $services = $services->fetchAll();

            $servicesList = array();
            for($j = 0; $j < count($services); $j = $j + 1){
                $servicesList[] = $services[$j]["name"];
            }
            
            $points[$i]["services"] =  implode(", ", $servicesList);
        }

        return new Response(json_encode($points));
    }

    /**
     * @Route("/awards/")
     * @Method("POST")
     */
    public function awardsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $awards = $em->getConnection()->prepare("SELECT * FROM award WHERE active = true");
        $awards->execute();
        $awards = $awards->fetchAll();

        return new Response(json_encode($awards));
    }

    /**
     * @Route("/user-points/")
     * @Method("POST")
     */
    public function userPointsAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $user = $request->request->get("user");

        $points = $em->getConnection()->prepare("SELECT SUM(points) points FROM user_point WHERE user = ?");
        $points->execute(array($user));
        $points = $points->fetch();

        return new Response(json_encode($points["points"] ? $points["points"] : 0));
    }

    /**
     * @Route("/award-request/")
     * @Method("POST")
     */
    public function awardRequestAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $user = $request->request->get("user");
        $award = $request->request->get("award");
        $address = $request->request->get("address");

        try{
            if(empty($user) || empty($award) || empty($address)){
                throw new \Exception("Dados incompletos", 500);
            }

            $user = $em->getRepository("BlzBundle:User")->findOneById($user);

            if(empty($user)){
                throw new \Exception("Usuário não encontrado", 404);
            }

            $award = $em->getRepository("BlzBundle:Award")->findOneBy(array(
                "id" => $award,
                "active" => true
            ));

            if(empty($award)){
                throw new \Exception("Premiação não encontrada", 404);
            }

            $address = $em->getRepository("BlzBundle:Address")->findOneById($address);

            if(empty($user)){
                throw new \Exception("Endereço não encontrado", 404);
            }

            $checkAwardRequest = $em->getRepository("BlzBundle:AwardRequest")->findOneBy(array(
                "user" => $user,
                "delivered" => false
            ));

            if(!empty($checkAwardRequest)){
                throw new \Exception("Você já tem uma solicitação de premiação em andamento", 500);
            }

            $points = $em->getConnection()->prepare("SELECT SUM(points) points FROM user_point WHERE user = ?");
            $points->execute(array($user->getId()));
            $points = $points->fetch();

            if($points["points"] < $award->getPoints()){
                throw new \Exception("Você não tem pontos o suficiente para essa premiação", 500);
            }

            $awardRequest = new AwardRequest;
            $awardRequest->setUser($user);
            $awardRequest->setAward($award);
            $awardRequest->setAddress($address);
            $awardRequest->setPoints($award->getPoints());

            $em->persist($awardRequest);
            $em->flush();

            return new Response(json_encode("success"));
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            return new Response($message, $status);
        }
    }

    /**
     * @Route("/login-provider/")
     * @Method("POST")
     */
    public function loginProviderAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $email = $request->request->get("email");
        $password = $request->request->get("password");

        try{
            if(empty($email) || empty($password)){
                throw new \Exception("Dados incompletos", 500);
            }

            $provider = $em->getRepository("BlzBundle:User")->findOneBy(array(
                "email" => $email,
                "password" => md5($password),
                "type" => $em->getRepository("BlzBundle:UserType")->findOneById(3)
            ));

            if(empty($provider)){
                throw new \Exception("Login ou senha incorretos", 403);
            }

            if(empty($provider->getActive())){
                throw new \Exception("Usuário bloqueado", 403);
            }

            return new Response(json_encode(array(
                "id" => $provider->getId(),
                "name" => $provider->getName(),
                "email" => $provider->getEmail(),
                "photo" => $provider->getProviderData()->getPhoto(),
            )));
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            return new Response($message, $status);
        }
    }

    /**
     * @Route("/check-provider/")
     * @Method("POST")
     */
    public function checkProviderAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");

        try{
            if(empty($id)){
                throw new \Exception("Dados incompletos", 500);
            }

            $user = $em->getRepository("BlzBundle:User")->findOneBy(array(
                "id" => $id,
                "type" => $em->getRepository("BlzBundle:UserType")->findOneById(3)
            ));

            if(empty($user)){
                throw new \Exception("Usuário não encontrado", 404);
            }

            $data = $user->getProviderData();
            $address = $data->getAddress();

            if((empty($data->getCpf()) && empty($data->getCnpj())) || (empty($data->getPhone()) && empty($data->getCelphone())) || empty($data->getBirth()) || empty($address->getCep()) || empty($address->getStreet()) || empty($address->getNeighborhood()) || empty($address->getNumber()) || empty($address->getCity())){
                return new Response(json_encode(array("success" => false)));
            }

            return new Response(json_encode(array("success" => true)));
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            return new Response($message, $status);
        }
    }

    /**
     * @Route("/get-provider/")
     * @Method("POST")
     */
    public function getProviderAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");

        $provider = $em->getConnection()->prepare("SELECT u.id, d.photo, u.name, u.privacy_accepted, d.name sponsorName, d.description, d.birth, u.email, d.cpf, d.cnpj, d.phone, d.celphone, a.cep, a.street, a.neighborhood, a.number, c.id city, s.uf state, d.home_care homeCare
        FROM user u 
        INNER JOIN provider_data d ON u.provider_data = d.id 
        INNER JOIN address a ON d.address = a.id
        INNER JOIN city c ON a.city = c.id
        INNER JOIN state s ON c.state = s.id
        WHERE u.type = 3 AND u.id = ?");
        $provider->execute(array($id));
        $provider = $provider->fetch();

        if(preg_match('/^(\d{2})(\d{4})(\d{4})$/', $provider["phone"], $matches)){
            $provider["phone"] = '(' . $matches[1] . ') ' .$matches[2] . '-' . $matches[3];
        }

        if(preg_match( '/^(\d{2})(\d{1})(\d{4})(\d{4})$/', $provider["celphone"], $matches)){
            $provider["celphone"] = '(' . $matches[1] . ') ' .$matches[2] . $matches[3] . '-' . $matches[4];
        }

        if(preg_match('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', $provider["cpf"], $matches)){
            $provider["cpf"] = $matches[1] .  "." . $matches[2] . "." . $matches[3] . "-" . $matches[4];
        }

        if(preg_match('/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/', $provider["cnpj"], $matches)){
            $provider["cnpj"] = $matches[1] .  "." . $matches[2] . "." . $matches[3] . "/" . $matches[4] . "-" . $matches[5];
        }

        return new Response(json_encode($provider));
    }

    /**
     * @Route("/edit-provider/")
     * @Method("POST")
     */
    public function editProviderAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $id = $request->request->get("id");
        $name = $request->request->get("name");
        $sponsorName = $request->request->get("sponsor_name");
        $description = $request->request->get("description");
        $birth = $request->request->get("birth");
        $password = $request->request->get("password");
        $cpf = $request->request->get("cpf");
        $cnpj = $request->request->get("cnpj");
        $phone = $request->request->get("phone");
        $celphone = $request->request->get("celphone");
        $cep = $request->request->get("cep");
        $street = $request->request->get("street");
        $neighborhood = $request->request->get("neighborhood");
        $number = $request->request->get("number");
        $city = $request->request->get("city");
        $coordX = $request->request->get("coord_x");
        $coordY = $request->request->get("coord_y");
        $homeCare = !empty($request->request->get("home_care")) ? true : false;
        $photo = $request->request->get("photo");

        try{
            if(empty($id) || empty($name) || empty($sponsorName) || empty($description) || empty($birth) || empty($cep) || empty($street) || empty($neighborhood) || empty($number) || empty($city)){
                throw new \Exception("Dados incompletos", 500);
            }

            if(empty($cpf) && empty($cnpj)){
                throw new \Exception("É necessário informar CPF ou CNPJ", 500);
            }

            if(empty($phone) && empty($celphone)){
                throw new \Exception("É necessário informar Telefone ou celular", 500);
            }

            if(empty($coordX) || empty($coordY)){
                throw new \Exception("Não foi possível localizar o endereço", 500);
            }

            $city = $em->getRepository("BlzBundle:City")->findOneById($city);

            if(empty($city)){
                throw new \Exception("Cidade não encontrada", 404);
            }

            $user = $em->getRepository("BlzBundle:User")->findOneBy(array(
                "id" => $id,
                "active" => true,
                "type" => $em->getRepository("BlzBundle:UserType")->findOneById(3)
            ));

            if(empty($user)){
                throw new \Exception("Prestador de serviço não encontrado", 404);
            }

            $data = $user->getProviderData();
            $data->setName($sponsorName);
            $data->setDescription($description);
            $data->setBirth(new \Datetime($birth));
            $data->setCpf(preg_replace("/([^0-9]+)/", "", $cpf));
            $data->setCnpj(preg_replace("/([^0-9]+)/", "", $cnpj));
            $data->setPhone(preg_replace("/([^0-9]+)/", "", $phone));
            $data->setCelphone(preg_replace("/([^0-9]+)/", "", $celphone));
            $data->setCoordX($coordX);
            $data->setCoordY($coordY);
            $data->setHomeCare($homeCare);

            if(!empty($photo)){
                $filename = md5(uniqid()) . ".png";
                $photo = base64_decode(str_replace(" ", "+", str_replace("data:image/png;base64,", "", $photo)));
    
                if(file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/upload/provider/" . $filename, $photo)){
                    if(is_file($_SERVER["DOCUMENT_ROOT"] . "/upload/provider/" . $data->getPhoto())){
                        unlink($_SERVER["DOCUMENT_ROOT"] . "/upload/provider/" . $data->getPhoto());
                    }
                    $data->setPhoto($filename);
                }
                else{
                    throw new \Exception("Ocorreu um erro no upload da foto", 500);
                }
            }

            $address = $data->getAddress();
            $address->setCep(preg_replace("/([^0-9]+)/", "", $cep));
            $address->setStreet($street);
            $address->setNeighborhood($neighborhood);
            $address->setNumber($number);
            $address->setCity($city);

            $user->setName($name);
            
            if(!empty($password)){
                $user->setPassword(md5($password));
            }

            $em->flush();

            $em->getConnection()->commit();
            return new Response(json_encode("success"));
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            $em->getConnection()->rollBack();
            return new Response($message, $status);
        }
    }

    /**
     * @Route("/get-payment-methods/")
     * @Method("POST")
     */
    public function getPaymentMethodsAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");

        $paymentMethods = $em->getConnection()->prepare("SELECT p.id, p.name, IF(pp.id IS NULL, FALSE, TRUE) checked FROM payment_method p LEFT JOIN provider_payment pp ON pp.payment_method = p.id AND pp.provider = ?");
        $paymentMethods->execute(array($id));
        $paymentMethods = $paymentMethods->fetchAll();

        return new Response(json_encode($paymentMethods));
    }

    /**
     * @Route("/payment-methods/")
     * @Method("POST")
     */
    public function paymentMethodsAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $id = $request->request->get("id");
        $paymentMethods = $request->request->get("payment_method") ? $request->request->get("payment_method") : array();

        try{
            if(empty($id)){
                throw new \Exception("Dados incompletos", 500);
            }

            $provider = $em->getRepository("BlzBundle:User")->findOneBy(array(
                "id" => $id,
                "active" => true
            ));

            if(empty($provider)){
                throw new \Exception("Usuário não encontrado", 404);
            }

            for($i = 0; $i < count($paymentMethods); $i = $i + 1){
                $paymentMethod = $em->getRepository("BlzBundle:PaymentMethod")->findOneById($paymentMethods[$i]);

                $checkProviderPayment = $em->getRepository("BlzBundle:ProviderPayment")->findOneBy(array(
                    "provider" => $provider,
                    "paymentMethod" => $paymentMethod
                ));
                
                if(empty($checkProviderPayment)){
                    $providerPayment = new ProviderPayment;
                    $providerPayment->setProvider($provider);
                    $providerPayment->setPaymentMethod($paymentMethod);
                    
                    $em->persist($providerPayment); 
                }
            }

            $deleteProviderServicePrice = $em->getConnection()->prepare("DELETE psp FROM provider_service_price psp INNER JOIN provider_payment p ON psp.provider_payment = p.id WHERE p.payment_method NOT IN (?) AND provider = ?");
            $deleteProviderServicePrice->execute(array(implode(", ", $paymentMethods), $this->getUser()->getId()));

            $deletePaymentMethods = $em->getConnection()->prepare("DELETE FROM provider_payment WHERE payment_method NOT IN (?) AND provider = ?");
            $deletePaymentMethods->execute(array(implode(", ", $paymentMethods), $this->getUser()->getId()));

            $em->flush();

            $em->getConnection()->commit();
            return new Response(json_encode("success"));
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            $em->getConnection()->rollBack();
            return new Response($message, $status);
        }
    }

    /**
     * @Route("/ads/")
     * @Method("POST")
     */
    public function getAdsAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");

        $ads = $em->getConnection()->prepare("SELECT id, name, photo, DATE_FORMAT(validity, '%d/%m/%Y') validity FROM ad WHERE active = true AND provider = ?");
        $ads->execute(array($id));
        $ads = $ads->fetchAll();

        return new Response(json_encode($ads));
    }

    /**
     * @Route("/ad/create/")
     * @Method("POST")
     */
    public function createAdAction(Request $request)
    {
        $request->request->replace($this->getContent($request));
        
        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");
        $name = $request->request->get("name");
        $description = $request->request->get("description");
        $photo = $request->request->get("photo");

        try{
            if(empty($id) || empty($name) || empty($description)){
                throw new \Exception("Dados incompletos", 500);
            }

            $provider = $em->getRepository("BlzBundle:User")->findOneBy(array(
                "id" => $id,
                "active" => true
            ));

            if(empty($provider)){
                throw new \Exception("Profissional não encontrado", 404);
            }

            $adsActive = $em->getConnection()->prepare("SELECT COUNT(id) total FROM ad WHERE active = true AND DATE_FORMAT(CURDATE() ,'%Y-%m-01') AND provider = :provider");
            $adsActive->execute(array(
                "provider" => $provider->getId()
            ));
            $adsActive = $adsActive->fetch();

            $plan = $provider->getProviderData()->getPlan();
            
            if($plan->getNumberOfAds() <= $adsActive["total"]){
                throw new \Exception("O plano " . $plan->getName() . " não permite mais serviços. (" . $adsActive["total"] . "/" . $plan->getNumberOfAds() . ")", 500);
            }

            if(empty($photo)){
                throw new \Exception("É necessário uma imagem para o anúncio", 500);
            }

            $ad = new Ad;
            $ad->setName($name);
            $ad->setDescription($description);
            $ad->setValidity(new \DateTime("last day of this month"));
            $ad->setProvider($provider);

            $filename = md5(uniqid()) . ".png";
            $photo = base64_decode(str_replace(" ", "+", str_replace("data:image/png;base64,", "", $photo)));

            if(file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/upload/ad/" . $filename, $photo)){
                $ad->setPhoto($filename);
            }
            else{
                throw new \Exception("Ocorreu um erro no upload da foto", 500);
            }

            $em->persist($ad);
            $em->flush();

            return new Response(json_encode(array(
                "id" => $ad->getId(),
                "name" => $ad->getName(),
                "validity" => $ad->getValidity()->format("d/m/Y")
            )));
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            return new Response($message, $status);
        }
    }

    /**
     * @Route("/ad/delete/")
     * @Method("POST")
     */
    public function deleteAdAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");

        try{
            if(empty($id)){
                throw new \Exception("Dados incompletos", 500);
            }

            $ad = $em->getRepository("BlzBundle:Ad")->findOneBy(array(
                "id" => $id,
                "active" => true
            ));

            if(empty($ad)){
                throw new \Exception("Anúncio não encontrado", 404);
            }

            $ad->setActive(false);

            $em->flush();

            return new Response(json_encode("success"));
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            return new Response($message, $status);
        }
    }

    /**
     * @Route("/ad-provider/")
     * @Method("POST")
     */
    public function getAdAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");

        $ad = $em->getConnection()->prepare("SELECT id, name, photo, description FROM ad WHERE active = true AND id = ?");
        $ad->execute(array($id));
        $ad = $ad->fetch();

        return new Response(json_encode($ad));
    }

    /**
     * @Route("/ad/edit/")
     * @Method("POST")
     */
    public function editAdAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");
        $name = $request->request->get("name");
        $description = $request->request->get("description");
        $photo = $request->request->get("photo");

        try{
            if(empty($name) || empty($description) || empty($id)){
                throw new \Exception("Dados incompletos", 500);
            }

            $ad = $em->getRepository("BlzBundle:Ad")->findOneById($id);

            if(empty($ad)){
                throw new \Exception("Anúncio não encontrado", 404);
            }

            $ad->setName($name);
            $ad->setDescription($description);

            if(!empty($photo)){
                $filename = md5(uniqid()) . ".png";
                $photo = base64_decode(str_replace(" ", "+", str_replace("data:image/png;base64,", "", $photo)));
    
                if(file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/upload/ad/" . $filename, $photo)){
                    if(is_file($_SERVER["DOCUMENT_ROOT"] . "/upload/ad/" . $ad->getPhoto())){
                        unlink($_SERVER["DOCUMENT_ROOT"] . "/upload/ad/" . $ad->getPhoto());
                    }
                    $ad->setPhoto($filename);
                }
                else{
                    throw new \Exception("Ocorreu um erro no upload da foto", 500);
                }
            }

            $em->flush();

            return new Response(json_encode("success"));
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            return new Response($message, $status);
        }
    }

    /**
     * @Route("/service-times/")
     * @Method("POST")
     */
    public function getServiceTimesAction()
    {
        return new Response(json_encode($this->serviceTimes));
    }

    /**
     * @Route("/service-payments/")
     * @Method("POST")
     */
    public function getServicePaymentsAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $user = $request->request->get("user");

        $payments = $em->getConnection()->prepare("SELECT pp.id, pm.name payment_method FROM provider_payment pp
        INNER JOIN payment_method pm ON pp.payment_method = pm.id
        WHERE pp.provider = ?");
        $payments->execute(array($user));
        $payments = $payments->fetchAll();

        return new Response(json_encode($payments));
    }

    /**
     * @Route("/services/")
     * @Method("POST")
     */
    public function getServicesAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");

        $services = $em->getConnection()->prepare("SELECT p.id, p.name, p.active, s.name specialty, c.name category FROM provider_service p
        INNER JOIN specialty s ON p.specialty = s.id
        INNER JOIN category c ON s.category = c.id
        WHERE deleted = false AND p.user = ?");
        $services->execute(array($id));
        $services = $services->fetchAll();

        return new Response(json_encode($services));
    }

    /**
     * @Route("/specialty/")
     * @Method("POST")
     */
    public function getSpecialtyAction()
    {
        $em = $this->getDoctrine()->getManager();

        $specialty = $em->getConnection()->prepare("SELECT s.id, s.name specialty, c.name category FROM specialty s
        INNER JOIN category c ON s.category = c.id
        WHERE s.active = true AND c.active = true");
        $specialty->execute();
        $specialty = $specialty->fetchAll();

        return new Response(json_encode($specialty));
    }

    /**
     * @Route("/service/create/")
     * @Method("POST")
     */
    public function creatServiceAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");
        $name = $request->request->get("name");
        $description = $request->request->get("description");
        $time = $request->request->get("time");
        $specialty = $request->request->get("specialty");
        $priority = $request->request->get("priority");

        try{
            if(empty($name) || empty($description) || $time === "" || empty($specialty)){
                throw new \Exception("Dados incompletos", 500);
            }

            $provider = $em->getRepository("BlzBundle:User")->findOneBy(array(
                "id" => $id,
                "active" => true
            ));

            if(empty($provider)){
                throw new \Exception("Profissional não encontrado", 404);
            }

            $servicesActive = $em->getConnection()->prepare("SELECT COUNT(id) total FROM provider_service WHERE active = true AND deleted = false AND user = :user");
            $servicesActive->execute(array(
                "user" => $provider->getId()
            ));
            $servicesActive = $servicesActive->fetch();

            $plan = $provider->getProviderData()->getPlan();
            
            if($plan->getNumberOfServices() <= $servicesActive["total"]){
                throw new \Exception("O plano " . $plan->getName() . " não permite mais serviços. (" . $servicesActive["total"] . "/" . $plan->getNumberOfServices() . ")", 500);
            }

            $specialty = $em->getRepository("BlzBundle:Specialty")->findOneById($specialty);

            if(empty($specialty)){
                throw new \Exception("Especialidade não encontrada", 404);
            }

            $providerPayment = $em->getRepository("BlzBundle:ProviderPayment")->findByProvider($provider);

            if(empty($providerPayment)){
                throw new \Exception("É necessário inserir seus métodos de pagamento", 500);
            }

            $service = new ProviderService;
            $service->setName($name);
            $service->setDescription($description);
            $service->setUser($provider);
            $service->setTime($time);
            $service->setSpecialty($specialty);

            if(!empty($priority)){
                $service->setPriority($priority);
            }

            $em->persist($service);

            for($i = 0; $i < count($providerPayment); $i = $i + 1){
                $providerServicePrice = $em->getRepository("BlzBundle:ProviderServicePrice")->findOneBy(array(
                    "providerService" => $service,
                    "providerPayment" => $providerPayment[$i]
                ));

                $price = $request->request->get("price_" . $providerPayment[$i]->getId());

                if(empty($price)){
                    throw new \Exception("É necessário inserir o preço em " . $providerPayment[$i]->getPaymentMethod()->getName(), 500);
                }

                $providerServicePrice = new ProviderServicePrice;
                $providerServicePrice->setPrice(preg_replace(array("/([^0-9\,]+)/", "/(,)/"), array("", "."), $price));
                $providerServicePrice->setProviderService($service);
                $providerServicePrice->setProviderPayment($providerPayment[$i]);
            
                $em->persist($providerServicePrice);
            }

            $em->flush();

            return new Response(json_encode(array(
                "id" => $service->getId(),
                "name" => $service->getName(),
                "price" => $price,
                "category" => $service->getSpecialty()->getName(),
                "specialty" => $service->getSpecialty()->getCategory()->getName()
            )));
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            return new Response($message, $status);
        }
    }

    /**
     * @Route("/service/toggle/")
     * @Method("POST")
     */
    public function toggleServiceAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");

        try{
            if(empty($id)){
                throw new \Exception("Dados incompletos", 500);
            }

            $service = $em->getRepository("BlzBundle:ProviderService")->findOneBy(array(
                "id" => $id,
                "deleted" => false
            ));

            if(empty($service)){
                throw new \Exception("Serviço não encontrado", 404);
            }

            $service->setActive(!$service->getActive());

            $em->flush();

            return new Response(json_encode($service->getActive()));
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            return new Response($message, $status);
        }
    }

    /**
     * @Route("/service/delete/")
     * @Method("POST")
     */
    public function deleteServiceAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");

        try{
            if(empty($id)){
                throw new \Exception("Dados incompletos", 500);
            }

            $service = $em->getRepository("BlzBundle:ProviderService")->findOneBy(array(
                "id" => $id,
                "deleted" => false
            ));

            if(empty($service)){
                throw new \Exception("Serviço não encontrado", 404);
            }

            $service->setDeleted(true);

            $em->flush();

            return new Response(json_encode("success"));
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            return new Response($message, $status);
        }
    }

    /**
     * @Route("/service/")
     * @Method("POST")
     */
    public function getServiceAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");

        $service = $em->getConnection()->prepare("SELECT p.id, p.name, p.description, p.time, p.active, p.priority, p.user, s.id specialty FROM provider_service p
        INNER JOIN specialty s ON p.specialty = s.id
        WHERE deleted = false AND p.id = ?
        ORDER BY p.priority ASC");
        $service->execute(array($id));
        $service = $service->fetch();

        if(!empty($service)){
            $providerServicePrice = $em->getConnection()->prepare("SELECT pp.id, pm.name payment_method, REPLACE(ROUND(psp.price, 2), '.', ',') price FROM provider_payment pp
            INNER JOIN payment_method pm ON pp.payment_method = pm.id
            LEFT JOIN provider_service_price psp ON psp.provider_payment = pp.id AND psp.provider_service = ?
            WHERE pp.provider = ?");
            $providerServicePrice->execute(array($id, $service["user"]));
            $providerServicePrice = $providerServicePrice->fetchAll();

            $service["prices"] = $providerServicePrice;
        }

        $specialty = $em->getConnection()->prepare("SELECT s.id, s.name specialty, c.name category FROM specialty s
        INNER JOIN category c ON s.category = c.id
        WHERE s.active = true AND c.active = true");
        $specialty->execute();
        $specialty = $specialty->fetchAll();

        return new Response(json_encode(array(
            "service" => $service,
            "specialty" => $specialty
        )));
    }

    /**
     * @Route("/service/edit/")
     * @Method("POST")
     */
    public function editServiceAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");
        $name = $request->request->get("name");
        $description = $request->request->get("description");
        $time = $request->request->get("time");
        $specialty = $request->request->get("specialty");
        $priority = $request->request->get("priority");

        try{
            if(empty($id) || empty($name) || empty($description) || $time == ""){
                throw new \Exception("Dados incompletos", 500);
            }

            $service = $em->getRepository("BlzBundle:ProviderService")->findOneById(array(
                "id" => $id,
                "deleted" => false
            ));

            if(empty($service)){
                throw new \Exception("Serviço não encontrado", 404);
            }

            $specialty = $em->getRepository("BlzBundle:Specialty")->findOneById($specialty);

            if(empty($specialty)){
                throw new \Exception("Especialidade não encontrada", 404);
            }

            $providerPayment = $em->getRepository("BlzBundle:ProviderPayment")->findByProvider($service->getUser());

            if(empty($providerPayment)){
                throw new \Exception("É necessário inserir seus métodos de pagamento", 500);
            }

            $service->setName($name);
            $service->setDescription($description);
            $service->setTime($time);
            $service->setSpecialty($specialty);

            if(!empty($priority)){
                $service->setPriority($priority);
            }

            for($i = 0; $i < count($providerPayment); $i = $i + 1){
                $providerServicePrice = $em->getRepository("BlzBundle:ProviderServicePrice")->findOneBy(array(
                    "providerService" => $service,
                    "providerPayment" => $providerPayment[$i]
                ));

                $price = $request->request->get("price_" . $providerPayment[$i]->getId());

                if(empty($price)){
                    throw new \Exception("É necessário inserir o preço em " . $providerPayment[$i]->getPaymentMethod()->getName(), 500);
                }

                if(empty($providerServicePrice)){
                    $providerServicePrice = new ProviderServicePrice;
                    $providerServicePrice->setPrice(preg_replace(array("/([^0-9\,]+)/", "/(,)/"), array("", "."), $price));
                    $providerServicePrice->setProviderService($service);
                    $providerServicePrice->setProviderPayment($providerPayment[$i]);
                
                    $em->persist($providerServicePrice);
                }
                else{
                    $providerServicePrice->setPrice(preg_replace(array("/([^0-9\,]+)/", "/(,)/"), array("", "."), $price));
                }
            }

            $em->flush();

            return new Response(json_encode("success"));
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            return new Response($message, $status);
        }
    }

    /**
     * @Route("/push-provider/")
     * @Method("POST")
     */
    public function setPushProviderAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $provider = $request->request->get("user");
        $push = $request->request->get("push");

        try{
            if(empty($provider) || empty($push)){
                throw new \Exception("Dados incompletos", 500);
            }

            $provider = $em->getRepository("BlzBundle:User")->findOneById($provider);

            if(empty($provider)){
                throw new \Exception("Usuário não encontrado", 404);
            }

            $provider->getProviderData()->setPush($push);

            $em->flush();

            return new Response(json_encode("success"));
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            return new Response($message, $status);
        }
    }

    /**
     * @Route("/chat-room-provider/")
     * @Method("POST")
     */
    public function chatRoomProviderAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $user = $request->request->get("user");
        $provider = $request->request->get("provider");

        try{
            if(empty($user) || empty($provider)){
                throw new \Exception("Dados incompletos");
            }

            $chatRoom = $em->getConnection()->prepare("SELECT c.id, u.id user, u.name, photo, push FROM chat_room c
            INNER JOIN user u ON c.user = u.id
            INNER JOIN user_data d ON u.user_data = d.id
            WHERE user = :user AND provider = :provider");
            $chatRoom->execute(array(
                "user" => $user,
                "provider" => $provider
            ));
            $chatRoom = $chatRoom->fetch();

            if(empty($chatRoom)){
                $user = $em->getRepository("BlzBundle:User")->findOneBy(array(
                    "id" => $user,
                    "active" => true
                ));
    
                if(empty($user)){
                    throw new \Exception("Usuário não encontrado", 404);
                }
    
                $provider = $em->getRepository("BlzBundle:User")->findOneBy(array(
                    "id" => $provider,
                    "active" => true
                ));

                $chatRoom = new ChatRoom;
                $chatRoom->setUser($user);
                $chatRoom->setProvider($provider);
                
                $em->persist($chatRoom);
                $em->flush();

                $chatId = $chatRoom->getId();
                $userId = $user->getId();
                $name = $user->getName();
                $photo = $user->getUserData()->getPhoto();
                $push = $user->getUserData()->getPush();
                $chat = array();
            }
            else{
                $chatId = $chatRoom["id"];
                $userId = $chatRoom["user"];
                $name = $chatRoom["name"];
                $photo = $chatRoom["photo"];
                $push = $chatRoom["push"];
                $chat = $em->getConnection()->prepare("SELECT c.message, c.from_user, c.to_user, DATE_FORMAT(c.datetime, '%d/%m/%Y %H:%i') datetime FROM chat c WHERE chat_room = ?");
                $chat->execute(array($chatId));
                $chat = $chat->fetchAll();
            }

            return new Response(json_encode(array(
                "chat_room" => $chatId,
                "chat" => $chat,
                "user" => $userId,
                "name" => $name,
                "photo" => $photo,
                "push" => $push
            )));
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            return new Response($message, $status);
        }
    }

    /**
     * @Route("/chat-provider/list/")
     * @Method("POST")
     */
    public function chatProviderListAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $provider = $request->request->get("user");

        $chat = $em->getConnection()->prepare("SELECT u.id user, u.name, d.photo, last_message, DATE_FORMAT(last_datetime, '%d/%m/%Y %H:%i') last_datetime FROM chat_room c
        INNER JOIN user u ON c.user = u.id
        INNER JOIN user_data d ON u.user_data = d.id
        WHERE u.active = true AND c.active = true AND last_message IS NOT NULL AND last_datetime IS NOT NULL AND provider = ?");
        $chat->execute(array($provider));
        $chat = $chat->fetchAll();

        return new Response(json_encode($chat));
    }

    /**
     * @Route("/privacy-user/")
     * @Method("POST")
     */
    public function getPrivacyUserAction()
    {
        $em = $this->getDoctrine()->getManager();

        $privacy = $em->getConnection()->prepare("SELECT `value` FROM configuration WHERE `key` = 'user_privacy'");
        $privacy->execute();
        $privacy = $privacy->fetch();

        return new Response(json_encode($privacy["value"]));
    }

    /**
     * @Route("/privacy-provider/")
     * @Method("POST")
     */
    public function getPrivacyProviderAction()
    {
        $em = $this->getDoctrine()->getManager();

        $privacy = $em->getConnection()->prepare("SELECT `value` FROM configuration WHERE `key` = 'provider_privacy'");
        $privacy->execute();
        $privacy = $privacy->fetch();

        return new Response(json_encode($privacy["value"]));
    }

    /**
     * @Route("/accept-privacy-user/")
     * @Method("POST")
     */
    public function acceptPrivacyUserAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $user = $request->request->get("user");

        try{
            $user = $em->getRepository("BlzBundle:User")->findOneBy(array(
                "id" => $user,
                "active" => true
            ));

            if(empty($user)){
                throw new \Exception("Usuário não encontrado", 404);
            }

            $user->setPrivacyAccepted(true);

            $em->flush();

            return new Response($this->getProfile($user));
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            return new Response($message, $status);
        }
    }

    /**
     * @Route("/accept-privacy-provider/")
     * @Method("POST")
     */
    public function acceptPrivacyProviderAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $user = $request->request->get("user");

        try{
            $user = $em->getRepository("BlzBundle:User")->findOneBy(array(
                "id" => $user,
                "active" => true
            ));

            if(empty($user)){
                throw new \Exception("Usuário não encontrado", 404);
            }

            $user->setPrivacyAccepted(true);

            $em->flush();

            return new Response(json_encode("success"));
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            return new Response($message, $status);
        }
    }

    /**
     * @Route("/scheduling-provider/list/")
     * @Method("POST")
     */
    public function listSchedulingProviderAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $provider = $request->request->get("provider");
        $status = $request->request->get("status");

        $scheduling = $em->getConnection()->prepare("SELECT s.id, u.id user, u.name, d.photo, d.social, DATE_FORMAT(date, '%d/%m/%Y') date, TIME_FORMAT(time, '%H:%i') time FROM scheduling s
        INNER JOIN user u ON s.user = u.id
        INNER JOIN user_data d ON u.user_data = d.id
        WHERE s.provider = :provider AND s.status = :status
        ORDER BY s.id DESC");
        $scheduling->execute(array(
            "provider" => $provider,
            "status" => $status
        ));
        $scheduling = $scheduling->fetchAll();

        for($i = 0; $i < count($scheduling); $i = $i + 1){
            $services = $em->getConnection()->prepare("SELECT name FROM scheduling_service s
            INNER JOIN provider_service p ON s.service = p.id
            WHERE scheduling = ? ORDER BY p.priority ASC");
            $services->execute(array($scheduling[$i]["id"]));
            $services = $services->fetchAll();

            $servicesList = array();
            for($j = 0; $j < count($services); $j = $j + 1){
                $servicesList[] = $services[$j]["name"];
            }
            
            $scheduling[$i]["services"] =  implode(", ", $servicesList);
        }

        return new Response(json_encode($scheduling));
    }
    
    /**
     * @Route("/scheduling-provider/details/")
     * @Method("POST")
     */
    public function schedulingDetailsProviderAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");

        $scheduling = $em->getConnection()->prepare("SELECT s.id, s.home_care, ROUND(s.rating, 2) rating, s.notes, u.name user, u.id user_id, d.photo, d.social, d.phone, d.celphone, st.name status, st.id status_id, DATE_FORMAT(s.date, '%d/%m/%Y') date, TIME_FORMAT(s.time, '%H:%i') time, DATE_FORMAT(s.datetime, '%d/%m/%Y %H:%i') datetime, pm.name payment_method, pm.id payment_method_id, a.id address_id, a.cep, a.street, a.neighborhood, a.number, c.name city, sta.uf state, ap.cep cep_provider, ap.street street_provider, ap.neighborhood neighborhood_provider, ap.number number_provider, cp.name city_provider, stap.uf state_provider FROM scheduling s
        INNER JOIN user u ON s.user = u.id
        INNER JOIN user_data d ON u.user_data = d.id
        INNER JOIN user p ON s.provider = p.id
        INNER JOIN provider_data pd ON p.provider_data = pd.id
        INNER JOIN scheduling_status st ON s.status = st.id
        INNER JOIN payment_method pm ON s.payment_method = pm.id
        LEFT JOIN address a ON s.address = a.id
        LEFT JOIN city c ON a.city = c.id
        LEFT JOIN state sta ON c.state = sta.id
        LEFT JOIN address ap ON pd.address = ap.id
        LEFT JOIN city cp ON ap.city = cp.id
        LEFT JOIN state stap ON cp.state = stap.id
        WHERE s.id = ?");
        $scheduling->execute(array($id));
        $scheduling = $scheduling->fetch();

        if(preg_match('/^(\d{2})(\d{4})(\d{4})$/', $scheduling["phone"], $matches)){
            $scheduling["phone"] = '(' . $matches[1] . ') ' .$matches[2] . '-' . $matches[3];
        }

        if(preg_match( '/^(\d{2})(\d{1})(\d{4})(\d{4})$/', $scheduling["celphone"], $matches)){
            $scheduling["celphone"] = '(' . $matches[1] . ') ' .$matches[2] . $matches[3] . '-' . $matches[4];
        }

        $services = $em->getConnection()->prepare("SELECT name, description, REPLACE(ROUND(s.price, 2), '.', ',') price FROM scheduling_service s
        INNER JOIN provider_service p ON s.service = p.id
        WHERE scheduling = ? ORDER BY p.priority ASC");
        $services->execute(array($id));
        
        $scheduling["services"] = $services->fetchAll();

        $amount = $em->getConnection()->prepare("SELECT REPLACE(ROUND(SUM(s.price), 2), '.', ',') amount FROM scheduling_service s WHERE scheduling = ?");
        $amount->execute(array($id));

        $scheduling["amount"] = $amount->fetch()["amount"];

        return new Response(json_encode($scheduling));
    }

    /**
     * @Route("/scheduling-provider/cancel/")
     * @Method("POST")
     */
    public function schedulingCancelProviderAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");

        try{
            if(empty($id)){
                throw new \Exception("Dados incompletos", 500);
            }

            $scheduling = $em->getRepository("BlzBundle:Scheduling")->findOneById($id);

            if(empty($scheduling) || $scheduling->getStatus()->getId() != 1){
                throw new \Exception("Agendamento não encontrado", 404);
            }

            $scheduling->setStatus($em->getRepository("BlzBundle:SchedulingStatus")->findOneById(2));

            $em->flush();

            return new Response(json_encode(array(
                "id" => $scheduling->getId(),
                "user" => $scheduling->getUser()->getName(),
                "provider" => $scheduling->getProvider()->getProviderData()->getName(),
                "date" => $scheduling->getDate()->format("d/m/Y"),
                "time" => $scheduling->getTime()->format("H:i"),
                "home_care" => !empty($scheduling->getHomeCare()) ? "Sim" : "Não",
                "payment_method" => $scheduling->getPaymentMethod()->getName(),
                "push" => $scheduling->getUser()->getUserData()->getPush(),
            )));
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            return new Response($message, $status);
        }
    }

    /**
     * @Route("/scheduling-provider/finalize/")
     * @Method("POST")
     */
    public function schedulingFinalizeProviderAction(Request $request)
    {
        $request->request->replace($this->getContent($request));
        
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $id = $request->request->get("id");

        try{
            if(empty($id)){
                throw new \Exception("Dados incompletos", 500);
            }

            $systemTax = $em->getRepository("BlzBundle:Configuration")->findOneByKey("system_tax");
            $franchiseeTax = $em->getRepository("BlzBundle:Configuration")->findOneByKey("franchisee_tax");
            
            if(empty($systemTax) || empty($franchiseeTax)){
                throw new \Exception("Erro nos parâmetros de taxa", 500);
            }

            $scheduling = $em->getRepository("BlzBundle:Scheduling")->findOneById($id);

            if(empty($scheduling) || $scheduling->getStatus()->getId() != 1){
                throw new \Exception("Agendamento não encontrado", 404);
            }

            $schedulingServices = $em->getRepository("BlzBundle:SchedulingService")->findByScheduling($scheduling);

            $totalAmount = 0;
            $franchiseeAmount = 0;
            $systemAmount = 0;
            for($i = 0; $i < count($schedulingServices); $i = $i + 1){
                $totalAmount = $totalAmount + $schedulingServices[$i]->getPrice();
            }

            $franchiseeAmount = $totalAmount * ($franchiseeTax->getValue() / 100);
            $systemAmount = $franchiseeAmount * ($systemTax->getValue() / 100);

            $amountFinance = new Finance;
            $amountFinance->setAmount($totalAmount - $franchiseeAmount);
            $amountFinance->setUser($scheduling->getProvider());

            $em->persist($amountFinance);

            $franchiseeFinance = new Finance;
            $franchiseeFinance->setAmount($franchiseeAmount - $systemAmount);
            $franchiseeFinance->setUser($scheduling->getProvider()->getProviderData()->getFranchisee());

            $em->persist($franchiseeFinance);

            $systemFinance = new Finance;
            $systemFinance->setAmount($systemAmount);

            $em->persist($systemFinance);

            $scheduling->setAmount($amountFinance);
            $scheduling->setFranchiseeTax($franchiseeFinance);
            $scheduling->setSystemTax($systemFinance);
            $scheduling->setStatus($em->getRepository("BlzBundle:SchedulingStatus")->findOneById(3));

            $userPoints = new UserPoint;
            $userPoints->setPoints(round($totalAmount));
            $userPoints->setUser($scheduling->getUser());
            $userPoints->setScheduling($scheduling);

            $em->persist($userPoints);

            $providerPoints = new UserPoint;
            $providerPoints->setPoints(round($totalAmount));
            $providerPoints->setUser($scheduling->getProvider());
            $providerPoints->setScheduling($scheduling);

            $em->persist($providerPoints);

            $scheduling->getProvider()->getProviderData()->setPoints($scheduling->getProvider()->getProviderData()->getPoints() + $providerPoints->getPoints());
            
            $em->flush();

            $em->getConnection()->commit();
            return new Response(json_encode(array(
                "id" => $scheduling->getId(),
                "user" => $scheduling->getUser()->getName(),
                "provider" => $scheduling->getProvider()->getProviderData()->getName(),
                "date" => $scheduling->getDate()->format("d/m/Y"),
                "time" => $scheduling->getTime()->format("H:i"),
                "home_care" => !empty($scheduling->getHomeCare()) ? "Sim" : "Não",
                "payment_method" => $scheduling->getPaymentMethod()->getName(),
                "push" => $scheduling->getUser()->getUserData()->getPush(),
            )));
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            $em->getConnection()->rollBack();
            return new Response($message, $status);
        }
    }

    /**
     * @Route("/finances/")
     * @Method("POST")
     */
    public function getFinancesAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");

        $amount = $em->getConnection()->prepare("SELECT REPLACE(ROUND(SUM(a.amount), 2), '.', ',') amount, REPLACE(ROUND(SUM(st.amount) + SUM(ft.amount), 2), '.', ',') franchisee_tax FROM scheduling sc
        INNER JOIN finance a ON sc.amount = a.id
        INNER JOIN finance st ON sc.system_tax = st.id
        INNER JOIN finance ft ON sc.franchisee_tax = ft.id
        WHERE sc.status = 3 AND sc.provider = ?");
        $amount->execute(array($id));
        $amount = $amount->fetch();

        $finances = $em->getConnection()->prepare("SELECT REPLACE(ROUND(a.amount, 2), '.', ',') amount, REPLACE(ROUND(st.amount + ft.amount, 2), '.', ',') franchisee_tax, s.id scheduling FROM scheduling s
        INNER JOIN finance a ON s.amount = a.id
        INNER JOIN finance st ON s.system_tax = st.id
        INNER JOIN finance ft ON s.franchisee_tax = ft.id
        WHERE s.status = 3 AND s.provider = ?
        ORDER BY s.id DESC");
        $finances->execute(array($id));
        $finances = $finances->fetchAll();

        return new Response(json_encode(array(
            "amount" => $amount["amount"],
            "franchisee_tax" => $amount["franchisee_tax"],
            "finances" => $finances
        )));
    }

    /**
     * @Route("/times/")
     * @Method("POST")
     */
    public function getTimesAction()
    {
        return new Response(json_encode($this->times));
    }

    /**
     * @Route("/dates/")
     * @Method("POST")
     */
    public function getDatesAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");

        $dates = $em->getConnection()->prepare("SELECT DATE_FORMAT(date, '%Y') year, DATE_FORMAT(date, '%m') month, DATE_FORMAT(date, '%d') day FROM available_date WHERE user = ?");
        $dates->execute(array($id));
        $dates = $dates->fetchAll();

        return new Response(json_encode($dates));
    }

    /**
     * @Route("/schedule/date/")
     * @Method("POST")
     */
    public function getDateScheduleAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");
        $date = $request->request->get("date");

        try{
            if(empty($id) || empty($date)){
                throw new \Exception("Dados incompletos", 500);
            }

            $user = $em->getRepository("BlzBundle:User")->findOneBy(array(
                "id" => $id,
                "active" => true
            ));

            if(empty($user)){
                throw new \Exception("Usuário não encontrado", 404);
            }

            $availableDate = $em->getRepository("BlzBundle:AvailableDate")->findOneBy(array(
                "date" => new \DateTime($date),
                "user" => $user
            ));

            $times = array();

            if(!empty($availableDate)){
                $availableTime = $em->getConnection()->prepare("SELECT TIME_FORMAT(time, '%H:%i') time FROM available_time WHERE date = ?");
                $availableTime->execute(array($availableDate->getId()));
                $availableTime = $availableTime->fetchAll();

                for($i = 0; $i < count($availableTime); $i = $i + 1){
                    $times[] = $availableTime[$i]["time"];
                }
            }

            return new Response(json_encode($this->getTimesId($times)));
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            return new Response($message, $status);
        }
    }

    /**
     * @Route("/schedule/organize/")
     * @Method("POST")
     */
    public function scheduleAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $id = $request->request->get("id");
        $date = $request->request->get("date");
        $time = $request->request->get("time");

        try{
            if(empty($id) || empty($date)){
                throw new \Exception("Dados incompletos", 500);
            }

            $user = $em->getRepository("BlzBundle:User")->findOneBy(array(
                "id" => $id,
                "active" => true
            ));

            if(empty($user)){
                throw new \Exception("Usuário não encontrado", 404);
            }

            $availableDate = $em->getRepository("BlzBundle:AvailableDate")->findOneBy(array(
                "date" => new \DateTime($date),
                "user" => $user
            ));

            if(empty($availableDate)){
                $availableDate = new AvailableDate;
                $availableDate->setDate(new \DateTime($date));
                $availableDate->setUser($user);

                $em->persist($availableDate);
            }
            else{
                $resetTime = $em->getConnection()->prepare("DELETE FROM available_time WHERE date = ?");
                $resetTime->execute(array($availableDate->getId()));
            }

            if(empty($time)){
                $em->remove($availableDate);
            }
            else{
                for($i = 0; $i < count($time); $i = $i + 1){
                    if(!empty($this->times[$time[$i]])){
                        $availableTime = new AvailableTime;
                        $availableTime->setTime(new \DateTime($this->times[$time[$i]]));
                        $availableTime->setDate($availableDate);

                        $em->persist($availableTime);
                    }
                }
            }

            $em->flush();

            $em->getConnection()->commit();
            return new Response(json_encode("success"));
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            $em->getConnection()->rollBack();
            return new Response($message, $status);
        }
    }

    /**
     * @Route("/schedule/auto/")
     * @Method("POST")
     */
    public function scheduleAutoAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $id = $request->request->get("id");
        $dateMin = $request->request->get("date_min");
        $dateMax = $request->request->get("date_max");
        $dayMin = $request->request->get("day_min");
        $dayMax = $request->request->get("day_max");
        $timeMin = $request->request->get("time_min");
        $timeMax = $request->request->get("time_max");

        try{
            if(empty($id) || empty($dateMin) || empty($dateMax) || empty($dayMin) || empty($dayMax)){
                throw new \Exception("Dados incompletos", 500);
            }

            $dateMin = new \DateTime($dateMin);
            $dateMax = new \DateTime($dateMax);

            if($dateMin >= $dateMax){
                throw new \Exception("A data mínima deve ser menor que a máxima", 500);
            }

            if($dayMin >= $dayMax){
                throw new \Exception("O dia mínimo deve ser menor que o máximo", 500);
            }

            if($timeMin >= $timeMax){
                throw new \Exception("A hora mínima deve ser menor que a máxima", 500);
            }

            $user = $em->getRepository("BlzBundle:User")->findOneBy(array(
                "id" => $id,
                "active" => true
            ));

            if(empty($user)){
                throw new \Exception("Usuário não encontrado", 404);
            }

            while($dateMin <= $dateMax){
                $dayWeek = $dateMin->format("N");

                $availableDate = $em->getRepository("BlzBundle:AvailableDate")->findOneBy(array(
                    "date" => $dateMin,
                    "user" => $user
                ));

                if($dayWeek >= $dayMin && $dayWeek <= $dayMax){
                    if(empty($availableDate)){
                        $availableDate = new AvailableDate;
                        $availableDate->setDate($dateMin);
                        $availableDate->setUser($user);
        
                        $em->persist($availableDate);
                    }
                    else{
                        $resetTime = $em->getConnection()->prepare("DELETE FROM available_time WHERE date = ?");
                        $resetTime->execute(array($availableDate->getId()));
                    }
        
                    if(!empty($this->times[$timeMin]) && !empty($this->times[$timeMax])){
                        for($i = $timeMin; $i <= $timeMax; $i = $i + 1){
                            $availableTime = new AvailableTime;
                            $availableTime->setTime(new \DateTime($this->times[$i]));
                            $availableTime->setDate($availableDate);
    
                            $em->persist($availableTime);
                        }
                    }
                }
                else{
                    if(!empty($availableDate)){
                        $resetTime = $em->getConnection()->prepare("DELETE FROM available_time WHERE date = ?");
                        $resetTime->execute(array($availableDate->getId()));

                        $em->remove($availableDate);
                    }
                }

                $em->flush();

                $dateMin->modify("+1 day");
            }

            $em->getConnection()->commit();
            return new Response(json_encode("success"));
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            $em->getConnection()->rollBack();
            return new Response($message, $status);
        }
    }

    /**
     * @Route("/scheduling/notice/")
     */
    public function getSchedulingAction()
    {
        $em = $this->getDoctrine()->getManager();

        $scheduling = $em->getConnection()->prepare("SELECT s.id, u.name user, ud.push user_push, p.name provider, pd.push provider_push, pd.push_web, IF(s.date = CURDATE(), TRUE, FALSE) today FROM scheduling s
        INNER JOIN user u ON s.user = u.id
        INNER JOIN user_data ud ON u.user_data = ud.id
        INNER JOIN user p ON s.provider = p.id
        INNER JOIN provider_data pd ON p.provider_data = pd.id
        WHERE s.status = 1
        AND (
            (s.date = CURDATE() AND s.time >= DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 60 MINUTE), '%H:%i:%s') AND s.time < DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 61 MINUTE), '%H:%i:%s'))
            OR
            (s.date = DATE_ADD(CURDATE(), INTERVAL 1 DAY) AND s.time >= DATE_FORMAT(NOW(), '%H:%i:%s') AND s.time < DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 1 MINUTE), '%H:%i:%s'))
        )");
        $scheduling->execute();
        $scheduling = $scheduling->fetchAll();

        return new Response(json_encode($scheduling));
    }

    /**
     * @Route("/rate/")
     * @Method("POST")
     */
    public function rateAction(Request $request)
    {
        $request->request->replace($this->getContent($request));

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $rating = $request->request->get("rating");
        $scheduling = $request->request->get("scheduling");

        try{
            if(empty($rating) || empty($scheduling)){
                throw new \Exception("Dados incompletos", 500);
            }

            $scheduling = $em->getRepository("BlzBundle:Scheduling")->findOneById($scheduling);

            if(empty($scheduling)){
                throw new \Exception("Agendamento não encontrado", 404);
            }
 
            if($scheduling->getStatus()->getId() != 3){
                throw new \Exception("Somente agendamentos finalizados podem ser avaliados", 500);
            }

            $scheduling->setRating($rating);

            $em->flush();

            $avgRating = $em->getConnection()->prepare("SELECT AVG(rating) rating FROM scheduling WHERE status = 4 AND rating > 0 AND provider = ?");
            $avgRating->execute(array($scheduling->getProvider()->getId()));
            $avgRating = $avgRating->fetch();
            $avgRating = $avgRating["rating"];

            $scheduling->getProvider()->getProviderData()->setRating($avgRating);

            $em->flush();

            $em->getConnection()->commit();
            return new Response(json_encode("success"));
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            $em->getConnection()->rollBack();
            return new Response($message, $status);
        }
    }

    /**
     * @Route("/config/")
     */
    public function getConfigAction()
    {
        $em = $this->getDoctrine()->getManager();

        $configRgCpf = $em->getConnection()->prepare("SELECT * FROM `configuration` WHERE `key` = 'rg_cpf'");
        $configRgCpf->execute();
        $configRgCpf = $configRgCpf->fetch();

        $configPhone = $em->getConnection()->prepare("SELECT * FROM `configuration` WHERE `key` = 'phone'");
        $configPhone->execute();
        $configPhone = $configPhone->fetch();

        return new Response(json_encode(array(
            "rg_cpf" => $configRgCpf["value"],
            "phone" => $configPhone["value"]
        )));
    }
}
