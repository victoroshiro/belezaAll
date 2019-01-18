<?php

namespace BlzBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use BlzBundle\Entity\User;
use BlzBundle\Entity\ProviderData;
use BlzBundle\Entity\Address;
use BlzBundle\Entity\Ad;

/**
 * @Route("/franqueado")
 */
class FranchiseeController extends Controller
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

    /**
     * @Route("/", name="IndexFranchisee")
     */
    public function indexAction()
    {
        if(empty($this->getUser()->getPrivacyAccepted())){
            return $this->redirectToRoute("PrivacyFranchisee");
        }

        $em = $this->getDoctrine()->getManager();

        $provider = $em->getConnection()->prepare("SELECT COUNT(user.id) total FROM user INNER JOIN provider_data d on user.provider_data = d.id WHERE active = true AND type = 3 AND franchisee = ?");
        $provider->execute(array($this->getUser()->getId()));
        $provider = $provider->fetch();

        return $this->render('BlzBundle:Franchisee:index.html.twig', array(
            "provider" => $provider["total"]
        ));
    }

    /**
     * @Route("/login", name="LoginFranchisee")
     */
    public function loginAction()
    {
        $authUtils = $this->get("security.authentication_utils");
        
        $error = $authUtils->getLastAuthenticationError();

        $lastUsername = $authUtils->getLastUsername();

        return $this->render("BlzBundle:Franchisee:login.html.twig", array(
            "last_username" => $lastUsername,
            "error" => $error
        ));
    }

    /**
     * @Route("/prestador-servico/criar/", name="CreateProviderFranchisee")
     */
    public function createProviderPageAction()
    {
        if(empty($this->getUser()->getPrivacyAccepted())){
            return $this->redirectToRoute("PrivacyFranchisee");
        }

        $em = $this->getDoctrine()->getManager();

        $plan = $em->getRepository("BlzBundle:Plan")->findBy(array(
            "active" => true
        ));

        return $this->render("BlzBundle:Franchisee:provider-create.html.twig", array(
            "plan" => $plan
        ));
    }

    /**
     * @Route("/provider/create/")
     * @Method("POST")
     */
    public function createProviderAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $name = $request->request->get("name");
        $sponsorName = $request->request->get("sponsor_name");
        $plan = $request->request->get("plan");
        $email = $request->request->get("email");
        $password = $request->request->get("password");
        $photo = $request->request->get("photo");

        try{
            if(empty($name) || empty($sponsorName) || empty($plan) || empty($email) || empty($password)){
                throw new \Exception("Dados incompletos", 500);
            }

            $plan = $em->getRepository("BlzBundle:Plan")->findOneById($plan);

            if(empty($plan)){
                throw new \Exception("Plano não encontrado", 404);
            }

            $userType = $em->getRepository("BlzBundle:UserType")->findOneById(3);

            if(empty($userType)){
                throw new \Exception("Tipo de usuário não encontrado", 404);
            }

            $checkUser = $em->getRepository("BlzBundle:User")->findOneByEmail($email);

            if(!empty($checkUser)){
                throw new \Exception("Já existe um usuário com este email", 500);
            }

            $address = new Address;

            $em->persist($address);

            $data = new ProviderData;
            $data->setName($sponsorName);
            $data->setAddress($address);
            $data->setFranchisee($this->getUser());
            $data->setPlan($plan);

            $photo = json_decode($photo);

            if(count($photo) > 0){
                $filename = md5(uniqid()) . ".png";
                $photo[0] = base64_decode(str_replace(" ", "+", str_replace("data:image/png;base64,", "", $photo[0])));

                if(file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/upload/provider/" . $filename, $photo[0])){
                    $data->setPhoto($filename);
                }
                else{
                    throw new \Exception("Ocorreu um erro no upload da foto", 500);
                }
            }

            $em->persist($data);

            $user = new User;
            $user->setName($name);
            $user->setEmail($email);
            $user->setPassword(md5($password));
            $user->setType($userType);
            $user->setProviderData($data);

            $em->persist($user);
            $em->flush();

            $em->getConnection()->commit();
            return new Response("success");
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            $em->getConnection()->rollBack();
            return new Response($message, $status);
        }
    }

    /**
     * @Route("/prestadores-servico/", name="ListProviderFranchisee")
     */
    public function listProviderAction()
    {
        if(empty($this->getUser()->getPrivacyAccepted())){
            return $this->redirectToRoute("PrivacyFranchisee");
        }

        $em = $this->getDoctrine()->getManager();

        $provider = $em->createQueryBuilder()
        ->select("u")
        ->from("BlzBundle:User", "u")
        ->join("BlzBundle:ProviderData", "d", "WITH", "u.providerData = d")
        ->where("u.type = :type")
        ->andWhere("d.franchisee = :franchisee")
        ->orderBy("u.id", "DESC")
        ->setParameter("type", $em->getRepository("BlzBundle:UserType")->findOneById(3))
        ->setParameter("franchisee", $this->getUser());
        $provider = $provider->getQuery()->getResult();

        return $this->render("BlzBundle:Franchisee:provider-list.html.twig", array(
            "provider" => $provider
        ));
    }

    /**
     * @Route("/provider/toggle/")
     * @Method("POST")
     */
    public function toggleProviderAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");

        try{
            if(empty($id)){
                throw new \Exception("Dados incompletos", 500);
            }

            $provider = $em->createQueryBuilder()
            ->select("u")
            ->from("BlzBundle:User", "u")
            ->join("BlzBundle:ProviderData", "d", "WITH", "u.providerData = d")
            ->where("u.id = :id")
            ->andWhere("u.type = :type")
            ->andWhere("d.franchisee = :franchisee")
            ->setParameter("id", $id)
            ->setParameter("type", $em->getRepository("BlzBundle:UserType")->findOneById(3))
            ->setParameter("franchisee", $this->getUser());
            $provider = $provider->getQuery()->getOneOrNullResult();

            if(empty($provider)){
                throw new \Exception("Prestador de serviço não encontrado", 404);
            }

            $provider->setActive(!$provider->getActive());

            $em->flush();

            return new Response($provider->getActive() ? 1 : 0);
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            return new Response($message, $status);
        }
    }

    /**
     * @Route("/prestador-servico/{id}/")
     */
    public function editProviderPageAction($id)
    {
        if(empty($this->getUser()->getPrivacyAccepted())){
            return $this->redirectToRoute("PrivacyFranchisee");
        }

        $em = $this->getDoctrine()->getManager();

        $provider = $em->createQueryBuilder()
        ->select("u")
        ->from("BlzBundle:User", "u")
        ->join("BlzBundle:ProviderData", "d", "WITH", "u.providerData = d")
        ->where("u.id = :id")
        ->andWhere("u.active = true")
        ->andWhere("u.type = :type")
        ->andWhere("d.franchisee = :franchisee")
        ->setParameter("id", $id)
        ->setParameter("type", $em->getRepository("BlzBundle:UserType")->findOneById(3))
        ->setParameter("franchisee", $this->getUser());
        $provider = $provider->getQuery()->getOneOrNullResult();

        $states = $em->getRepository("BlzBundle:State")->findAll();
        
        if(empty($provider->getProviderData()->getAddress()->getCity())){
            $sp = $em->getRepository("BlzBundle:State")->findOneById(26);
            $city = $em->getRepository("BlzBundle:City")->findByState($sp);
        }
        else{
            $city = $em->getRepository("BlzBundle:City")->findByState($provider->getProviderData()->getAddress()->getCity()->getState());
        }

        $franchisee = $em->getRepository("BlzBundle:User")->findBy(array(
            "active" => true,
            "type" => $em->getRepository("BlzBundle:UserType")->findOneById(2)
        ));

        $plan = $em->getRepository("BlzBundle:Plan")->findBy(array(
            "active" => true
        ));

        return $this->render("BlzBundle:Franchisee:provider-edit.html.twig", array(
            "provider" => $provider,
            "states" => $states,
            "city" => $city,
            "franchisee" => $franchisee,
            "plan" => $plan
        ));
    }

    /**
     * @Route("/provider/edit/")
     * @Method("POST")
     */
    public function editProviderAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $id = $request->request->get("id");
        $name = $request->request->get("name");
        $sponsorName = $request->request->get("sponsor_name");
        $description = $request->request->get("description");
        $plan = $request->request->get("plan");
        $birth = $request->request->get("birth");
        $email = $request->request->get("email");
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
            if(empty($id) || empty($name) || empty($sponsorName) || empty($description) || empty($birth) || empty($email) || empty($cep) || empty($street) || empty($neighborhood) || empty($number) || empty($city)){
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

            $plan = $em->getRepository("BlzBundle:Plan")->findOneById($plan);

            if(empty($plan)){
                throw new \Exception("Plano não encontrado", 404);
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

            $checkUser = $em->getRepository("BlzBundle:User")->findOneByEmail($email);

            if(!empty($checkUser) && $email != $user->getEmail()){
                throw new \Exception("Já existe um usuário com este email", 500);
            }

            $data = $user->getProviderData();
            $data->setName($sponsorName);
            $data->setDescription($description);
            $data->setBirth((new \Datetime)->createFromFormat("d/m/Y", $birth));
            $data->setCpf(preg_replace("/([^0-9]+)/", "", $cpf));
            $data->setCnpj(preg_replace("/([^0-9]+)/", "", $cnpj));
            $data->setPhone(preg_replace("/([^0-9]+)/", "", $phone));
            $data->setCelphone(preg_replace("/([^0-9]+)/", "", $celphone));
            $data->setCoordX($coordX);
            $data->setCoordY($coordY);
            $data->setHomeCare($homeCare);
            $data->setPlan($plan);

            $photo = json_decode($photo);

            if(count($photo) > 0){
                $filename = md5(uniqid()) . ".png";
                $photo[0] = base64_decode(str_replace(" ", "+", str_replace("data:image/png;base64,", "", $photo[0])));
    
                if(file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/upload/provider/" . $filename, $photo[0])){
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
            $user->setEmail($email);
            
            if(!empty($password)){
                $user->setPassword(md5($password));
            }

            $em->flush();

            $em->getConnection()->commit();
            return new Response("success");
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            $em->getConnection()->rollBack();
            return new Response($message, $status);
        }
    }

    /**
     * @Route("/prestador-servico/{id}/servicos/")
     */
    public function listServiceAction($id)
    {
        if(empty($this->getUser()->getPrivacyAccepted())){
            return $this->redirectToRoute("PrivacyFranchisee");
        }

        $em = $this->getDoctrine()->getManager();

        $provider = $em->createQueryBuilder()
        ->select("u")
        ->from("BlzBundle:User", "u")
        ->join("BlzBundle:ProviderData", "d", "WITH", "u.providerData = d")
        ->where("u.id = :id")
        ->andWhere("u.active = true")
        ->andWhere("u.type = :type")
        ->andWhere("d.franchisee = :franchisee")
        ->setParameter("id", $id)
        ->setParameter("type", $em->getRepository("BlzBundle:UserType")->findOneById(3))
        ->setParameter("franchisee", $this->getUser());
        $provider = $provider->getQuery()->getOneOrNullResult();

        if(empty($provider)){
            $service = array();
        }
        else{
            $service = $em->getRepository("BlzBundle:ProviderService")->findBy(array(
                "deleted" => false,
                "user" => $provider
            ), array(
                "priority" => "ASC"
            ));
        }

        return $this->render("BlzBundle:Franchisee:service-list.html.twig", array(
            "service" => $service,
            "provider" => $provider
        ));
    }

    /**
     * @Route("/service/delete/")
     * @Method("POST")
     */
    public function deleteServiceAction(Request $request)
    {
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

            if($service->getUser()->getProviderData()->getFranchisee()->getId() != $this->getUser()->getId()){
                throw new \Exception("Você não tem permissão para isso", 403);
            }

            $service->setActive(false);

            $em->flush();

            return new Response("success");
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
            
            if($service->getUser()->getProviderData()->getFranchisee()->getId() != $this->getUser()->getId()){
                throw new \Exception("Você não tem permissão para isso", 403);
            }

            $service->setActive(!$service->getActive());

            $em->flush();

            return new Response($service->getActive() ? 1 : 0);
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            return new Response($message, $status);
        }
    }

    /**
     * @Route("/servico/{id}/")
     */
    public function editServicoPageAction($id)
    {
        if(empty($this->getUser()->getPrivacyAccepted())){
            return $this->redirectToRoute("PrivacyFranchisee");
        }

        $em = $this->getDoctrine()->getManager();

        $service = $em->getRepository("BlzBundle:ProviderService")->findOneBy(array(
            "id" => $id,
            "deleted" => false
        ));

        if($service->getUser()->getProviderData()->getFranchisee()->getId() != $this->getUser()->getId()){
            $service = NULL;
            $providerServicePrice = array();
        }
        else{
            $providerServicePrice = $em->getConnection()->prepare("SELECT pp.id, pm.name payment_method, psp.price FROM provider_payment pp
            INNER JOIN payment_method pm ON pp.payment_method = pm.id
            LEFT JOIN provider_service_price psp ON psp.provider_payment = pp.id AND psp.provider_service = ?
            WHERE pp.provider = ?");
            $providerServicePrice->execute(array($service->getId(), $service->getUser()->getId()));
            $providerServicePrice = $providerServicePrice->fetchAll();
        }

        $specialty = $em->createQueryBuilder()
        ->select("s")
        ->from("BlzBundle:Specialty", "s")
        ->join("BlzBundle:Category", "c", "WITH", "s.category = c")
        ->where("s.active = true")
        ->andWhere("c.active = true");
        $specialty = $specialty->getQuery()->getResult();

        return $this->render("BlzBundle:Franchisee:service-edit.html.twig", array(
            "service" => $service,
            "specialty" => $specialty,
            "provider_service_price" => $providerServicePrice,
            "times" => $this->serviceTimes
        ));
    }

    /**
     * @Route("/service/edit/")
     * @Method("POST")
     */
    public function editServiceAction(Request $request)
    {
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

            $service = $em->getRepository("BlzBundle:ProviderService")->findOneById($id);

            if(empty($service)){
                throw new \Exception("Serviço não encontrado", 404);
            }

            if($service->getUser()->getProviderData()->getFranchisee()->getId() != $this->getUser()->getId()){
                throw new \Exception("Você não tem permissão para isso", 403);
            }

            $specialty = $em->getRepository("BlzBundle:Specialty")->findOneById($specialty);

            if(empty($specialty)){
                throw new \Exception("Especialidade não encontrada", 404);
            }

            $providerPayment = $em->getRepository("BlzBundle:ProviderPayment")->findByProvider($service->getUser());

            if(empty($providerPayment)){
                throw new \Exception("O profissional necessita inserir os métodos de pagamento dele", 500);
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

            return new Response("success");
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            return new Response($message, $status);
        }
    }

    /**
     * @Route("/prestador-servico/{id}/agenda/")
     */
    public function schedulePageAction($id)
    {   
        if(empty($this->getUser()->getPrivacyAccepted())){
            return $this->redirectToRoute("PrivacyFranchisee");
        }

        $em = $this->getDoctrine()->getManager();

        $provider = $em->createQueryBuilder()
        ->select("u")
        ->from("BlzBundle:User", "u")
        ->join("BlzBundle:ProviderData", "d", "WITH", "u.providerData = d")
        ->where("u.id = :id")
        ->andWhere("u.active = true")
        ->andWhere("u.type = :type")
        ->andWhere("d.franchisee = :franchisee")
        ->setParameter("id", $id)
        ->setParameter("type", $em->getRepository("BlzBundle:UserType")->findOneById(3))
        ->setParameter("franchisee", $this->getUser());
        $provider = $provider->getQuery()->getOneOrNullResult();

        $availableDate = $em->getRepository("BlzBundle:AvailableDate")->findByUser($provider);

        $dates = array();

        for($i = 0; $i < count($availableDate); $i = $i + 1){
            $dates[] = $availableDate[$i]->getDate()->format("Y-m-d");
        }

        return $this->render("BlzBundle:Franchisee:schedule.html.twig", array(
            "provider" => $provider,
            "dates" => $dates
        ));
    }

    /**
     * @Route("/schedule/date/")
     */
    public function getDateScheduleAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");
        $date = $request->request->get("date");

        try{
            if(empty($id) || empty($date)){
                throw new \Exception("Dados incompletos", 500);
            }

            $provider = $em->createQueryBuilder()
            ->select("u")
            ->from("BlzBundle:User", "u")
            ->join("BlzBundle:ProviderData", "d", "WITH", "u.providerData = d")
            ->where("u.id = :id")
            ->andWhere("u.active = true")
            ->andWhere("u.type = :type")
            ->andWhere("d.franchisee = :franchisee")
            ->setParameter("id", $id)
            ->setParameter("type", $em->getRepository("BlzBundle:UserType")->findOneById(3))
            ->setParameter("franchisee", $this->getUser());
            $provider = $provider->getQuery()->getOneOrNullResult();

            if(empty($provider)){
                throw new \Exception("Prestador de serviço não encontrado", 404);
            }

            $availableDate = $em->getRepository("BlzBundle:AvailableDate")->findOneBy(array(
                "date" => new \DateTime($date),
                "user" => $provider
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
     * @Route("/prestador-servico/{id}/servicos-agendados/")
     */
    public function schedulingAction($id)
    {     
        if(empty($this->getUser()->getPrivacyAccepted())){
            return $this->redirectToRoute("PrivacyFranchisee");
        }

        $em = $this->getDoctrine()->getManager();

        $provider = $em->createQueryBuilder()
        ->select("u")
        ->from("BlzBundle:User", "u")
        ->join("BlzBundle:ProviderData", "d", "WITH", "u.providerData = d")
        ->where("u.id = :id")
        ->andWhere("u.active = true")
        ->andWhere("u.type = :type")
        ->andWhere("d.franchisee = :franchisee")
        ->setParameter("id", $id)
        ->setParameter("type", $em->getRepository("BlzBundle:UserType")->findOneById(3))
        ->setParameter("franchisee", $this->getUser());
        $provider = $provider->getQuery()->getOneOrNullResult();

        $waiting = $em->getRepository("BlzBundle:Scheduling")->findBy(array(
            "provider" => $provider,
            "status" => $em->getRepository("BlzBundle:SchedulingStatus")->findOneById(1)
        ), array(
            "id" => "DESC"
        ));

        $canceled = $em->getRepository("BlzBundle:Scheduling")->findBy(array(
            "provider" => $provider,
            "status" => $em->getRepository("BlzBundle:SchedulingStatus")->findOneById(2)
        ), array(
            "id" => "DESC"
        ));

        $finalized = $em->getRepository("BlzBundle:Scheduling")->findBy(array(
            "provider" => $provider,
            "status" => $em->getRepository("BlzBundle:SchedulingStatus")->findOneById(3)
        ), array(
            "id" => "DESC"
        ));

        return $this->render("BlzBundle:Franchisee:scheduling.html.twig", array(
            "waiting" => $waiting,
            "canceled" => $canceled,
            "finalized" => $finalized,
            "provider" => $provider
        ));
    }

    /**
     * @Route("/agendamento/{id}/")
     */
    public function schedulingDetailsAction($id)
    {
        if(empty($this->getUser()->getPrivacyAccepted())){
            return $this->redirectToRoute("PrivacyFranchisee");
        }

        $em = $this->getDoctrine()->getManager();
        
        $scheduling = $em->getRepository("BlzBundle:Scheduling")->findOneBy(array(
            "id" => $id
        ));

        $provider = $em->createQueryBuilder()
        ->select("u")
        ->from("BlzBundle:User", "u")
        ->join("BlzBundle:ProviderData", "d", "WITH", "u.providerData = d")
        ->where("u.id = :id")
        ->andWhere("u.active = true")
        ->andWhere("u.type = :type")
        ->andWhere("d.franchisee = :franchisee")
        ->setParameter("id", $scheduling->getProvider()->getId())
        ->setParameter("type", $em->getRepository("BlzBundle:UserType")->findOneById(3))
        ->setParameter("franchisee", $this->getUser());
        $provider = $provider->getQuery()->getOneOrNullResult();

        if(!empty($provider)){
            $schedulingServices = $em->getRepository("BlzBundle:SchedulingService")->findByScheduling($scheduling);

            $scheduling->totalAmount = 0;
            for($i = 0; $i < count($schedulingServices); $i = $i + 1){
                $scheduling->totalAmount = $scheduling->totalAmount + $schedulingServices[$i]->getPrice();
            }
        }
        else{
            $scheduling = NULL;
            $schedulingServices = NULL;
        }

        return $this->render("BlzBundle:Franchisee:scheduling-details.html.twig", array(
            "scheduling" => $scheduling,
            "scheduling_services" => $schedulingServices
        ));
    }

    /**
     * @Route("/prestador-servico/{id}/financeiro/")
     */
    public function financesProviderAction($id)
    {   
        if(empty($this->getUser()->getPrivacyAccepted())){
            return $this->redirectToRoute("PrivacyFranchisee");
        }

        $em = $this->getDoctrine()->getManager();

        $provider = $em->createQueryBuilder()
        ->select("u")
        ->from("BlzBundle:User", "u")
        ->join("BlzBundle:ProviderData", "d", "WITH", "u.providerData = d")
        ->where("u.id = :id")
        ->andWhere("u.active = true")
        ->andWhere("u.type = :type")
        ->andWhere("d.franchisee = :franchisee")
        ->setParameter("id", $id)
        ->setParameter("type", $em->getRepository("BlzBundle:UserType")->findOneById(3))
        ->setParameter("franchisee", $this->getUser());
        $provider = $provider->getQuery()->getOneOrNullResult();

        $finances = $em->getRepository("BlzBundle:Scheduling")->findBy(array(
            "provider" => $provider,
            "status" => $em->getRepository("BlzBundle:SchedulingStatus")->findOneById(3)
        ), array(
            "id" => "DESC"
        ));

        return $this->render("BlzBundle:Franchisee:finances.html.twig", array(
            "finances" => $finances
        ));
    }

    /**
     * @Route("/clientes/", name="ListClientFranchisee")
     */
    public function listClientAction()
    {
        $em = $this->getDoctrine()->getManager();

        $client = $em->getRepository("BlzBundle:User")->findBy(array(
            "active" => true,
            "type" => $em->getRepository("BlzBundle:UserType")->findOneById(4)
        ), array(
            "id" => "DESC"
        ));

        return $this->render("BlzBundle:Franchisee:client-list.html.twig", array(
            "client" => $client
        ));
    }

    /**
     * @Route("/anuncio/criar/", name="CreateAdFranchisee")
     */
    public function createAdPageAction()
    {
        if(empty($this->getUser()->getPrivacyAccepted())){
            return $this->redirectToRoute("PrivacyFranchisee");
        }

        $em = $this->getDoctrine()->getManager();

        $provider = $em->createQueryBuilder()
        ->select("u")
        ->from("BlzBundle:User", "u")
        ->join("BlzBundle:ProviderData", "d", "WITH", "u.providerData = d")
        ->where("u.active = true")
        ->andWhere("u.type = :type")
        ->andWhere("d.franchisee = :franchisee")
        ->setParameter("type", $em->getRepository("BlzBundle:UserType")->findOneById(3))
        ->setParameter("franchisee", $this->getUser());
        $provider = $provider->getQuery()->getResult();

        return $this->render("BlzBundle:Franchisee:ad-create.html.twig", array(
            "provider" => $provider
        ));
    }

    /**
     * @Route("/ad/create/")
     * @Method("POST")
     */
    public function createAdAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $name = $request->request->get("name");
        $description = $request->request->get("description");
        $validity = $request->request->get("validity");
        $provider = $request->request->get("provider");
        $photo = $request->request->get("photo");

        try{
            if(empty($name) || empty($description) || empty($provider)){
                throw new \Exception("Dados incompletos", 500);
            }

            $provider = $em->createQueryBuilder()
            ->select("u")
            ->from("BlzBundle:User", "u")
            ->join("BlzBundle:ProviderData", "d", "WITH", "u.providerData = d")
            ->where("u.id = :id")
            ->andWhere("u.active = true")
            ->andWhere("u.type = :type")
            ->andWhere("d.franchisee = :franchisee")
            ->setParameter("id", $provider)
            ->setParameter("type", $em->getRepository("BlzBundle:UserType")->findOneById(3))
            ->setParameter("franchisee", $this->getUser());
            $provider = $provider->getQuery()->getOneOrNullResult();

            if(empty($provider)){
                throw new \Exception("Prestador de serviço não encontrado", 404);
            }

            $adsActive = $em->getConnection()->prepare("SELECT COUNT(id) total FROM ad WHERE active = true AND DATE_FORMAT(CURDATE() ,'%Y-%m-01') AND provider = :provider");
            $adsActive->execute(array(
                "provider" => $provider->getId()
            ));
            $adsActive = $adsActive->fetch();

            $plan = $provider->getProviderData()->getPlan();
            
            if($plan->getNumberOfAds() <= $adsActive["total"]){
                throw new \Exception("O plano " . $plan->getName() . " de" . $provider->getName() . " não permite mais serviços. (" . $adsActive["total"] . "/" . $plan->getNumberOfAds() . ")", 500);
            }

            $photo = json_decode($photo);

            if(count($photo) == 0){
                throw new \Exception("É necessário uma imagem para o anúncio", 500);
            }

            $ad = new Ad;
            $ad->setName($name);
            $ad->setDescription($description);
            $ad->setProvider($provider);

            if(!empty($validity)){
                $ad->setValidity((new \Datetime)->createFromFormat("d/m/Y", $validity));
            }
            else{
                $ad->setValidity(null);
            }

            $filename = md5(uniqid()) . ".png";
            $photo[0] = base64_decode(str_replace(" ", "+", str_replace("data:image/png;base64,", "", $photo[0])));

            if(file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/upload/ad/" . $filename, $photo[0])){
                $ad->setPhoto($filename);
            }
            else{
                throw new \Exception("Ocorreu um erro no upload da foto", 500);
            }

            $em->persist($ad);
            $em->flush();

            return new Response("success");
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            return new Response($message, $status);
        }
    }

    /**
     * @Route("/anuncios/", name="ListAdFranchisee")
     */
    public function listAdAction()
    {
        if(empty($this->getUser()->getPrivacyAccepted())){
            return $this->redirectToRoute("PrivacyFranchisee");
        }

        $em = $this->getDoctrine()->getManager();

        $provider = $em->createQueryBuilder()
        ->select("u")
        ->from("BlzBundle:User", "u")
        ->join("BlzBundle:ProviderData", "d", "WITH", "u.providerData = d")
        ->where("u.active = true")
        ->andWhere("u.type = :type")
        ->andWhere("d.franchisee = :franchisee")
        ->setParameter("type", $em->getRepository("BlzBundle:UserType")->findOneById(3))
        ->setParameter("franchisee", $this->getUser());
        $provider = $provider->getQuery()->getResult();

        $ad = $em->getRepository("BlzBundle:Ad")->findBy(array(
            "active" => true,
            "provider" => $provider
        ), array(
            "id" => "DESC"
        ));

        return $this->render("BlzBundle:Franchisee:ad-list.html.twig", array(
            "ad" => $ad
        ));
    }

    /**
     * @Route("/ad/delete/")
     * @Method("POST")
     */
    public function deleteAdAction(Request $request)
    {
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

            if($ad->getProvider()->getProviderData()->getFranchisee()->getId() != $this->getUser()->getId()){
                throw new \Exception("Você não tem permissão para isso", 403);
            }

            $ad->setActive(false);

            $em->flush();

            return new Response("success");
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            return new Response($message, $status);
        }
    }

    /**
     * @Route("/anuncio/{id}/")
     */
    public function editAdPageAction($id)
    {
        if(empty($this->getUser()->getPrivacyAccepted())){
            return $this->redirectToRoute("PrivacyFranchisee");
        }

        $em = $this->getDoctrine()->getManager();

        $ad = $em->getRepository("BlzBundle:Ad")->findOneBy(array(
            "id" => $id,
            "active" => true
        ));

        if($ad->getProvider()->getProviderData()->getFranchisee()->getId() != $this->getUser()->getId()){
            $ad = NULL;
        }

        $provider = $em->createQueryBuilder()
        ->select("u")
        ->from("BlzBundle:User", "u")
        ->join("BlzBundle:ProviderData", "d", "WITH", "u.providerData = d")
        ->where("u.active = true")
        ->andWhere("u.type = :type")
        ->andWhere("d.franchisee = :franchisee")
        ->setParameter("type", $em->getRepository("BlzBundle:UserType")->findOneById(3))
        ->setParameter("franchisee", $this->getUser());
        $provider = $provider->getQuery()->getResult();

        return $this->render("BlzBundle:Franchisee:ad-edit.html.twig", array(
            "ad" => $ad,
            "provider" => $provider
        ));
    }

    /**
     * @Route("/ad/edit/")
     * @Method("POST")
     */
    public function editAdAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");
        $name = $request->request->get("name");
        $description = $request->request->get("description");
        $validity = $request->request->get("validity");
        $photo = $request->request->get("photo");

        try{
            if(empty($name) || empty($description) || empty($id)){
                throw new \Exception("Dados incompletos", 500);
            }

            $ad = $em->getRepository("BlzBundle:Ad")->findOneById($id);

            if(empty($ad)){
                throw new \Exception("Anúncio não encontrado", 404);
            }

            if($ad->getProvider()->getProviderData()->getFranchisee()->getId() != $this->getUser()->getId()){
                throw new \Exception("Você não tem permissão para isso", 403);
            }

            $ad->setName($name);
            $ad->setDescription($description);

            if(!empty($validity)){
                $ad->setValidity((new \Datetime)->createFromFormat("d/m/Y", $validity));
            }

            $photo = json_decode($photo);

            if(count($photo) > 0){
                $filename = md5(uniqid()) . ".png";
                $photo[0] = base64_decode(str_replace(" ", "+", str_replace("data:image/png;base64,", "", $photo[0])));
    
                if(file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/upload/ad/" . $filename, $photo[0])){
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

            return new Response("success");
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            return new Response($message, $status);
        }
    }

    /**
     * @Route("/financeiro/", name="FinancesFranchisee")
     */
    public function financesAction()
    {
        if(empty($this->getUser()->getPrivacyAccepted())){
            return $this->redirectToRoute("PrivacyFranchisee");
        }

        $em = $this->getDoctrine()->getManager();

        $provider = $em->createQueryBuilder()
        ->select("u")
        ->from("BlzBundle:User", "u")
        ->join("BlzBundle:ProviderData", "d", "WITH", "u.providerData = d")
        ->where("u.active = true")
        ->andWhere("u.type = :type")
        ->andWhere("d.franchisee = :franchisee")
        ->orderBy("u.id", "DESC")
        ->setParameter("type", $em->getRepository("BlzBundle:UserType")->findOneById(3))
        ->setParameter("franchisee", $this->getUser());
        $provider = $provider->getQuery()->getResult();

        $finances = $em->getRepository("BlzBundle:Scheduling")->findBy(array(
            "provider" => $provider,
            "status" => $em->getRepository("BlzBundle:SchedulingStatus")->findOneById(3)
        ), array(
            "id" => "DESC"
        ));

        return $this->render("BlzBundle:Franchisee:finances.html.twig", array(
            "finances" => $finances
        ));
    }

    /**
     * @Route("/estatisticas/agendamentos/", name="StatsSchedulingFranchisee")
     */
    public function statsSchedulingAction(Request $request)
    {
        if(empty($this->getUser()->getPrivacyAccepted())){
            return $this->redirectToRoute("PrivacyFranchisee");
        }

        $em = $this->getDoctrine()->getManager();

        $dateMin = $request->request->get("date_min");
        $dateMax = $request->request->get("date_max");

        $date_min = (new \DateTime)->createFromFormat("d/m/Y", $dateMin);
        $date_max = (new \DateTime)->createFromFormat("d/m/Y", $dateMax);

        $scheduling = $em->getConnection()->prepare("SELECT u.name user, u.email, pd.name provider, p.email provider_email, s.datetime, ss.name status FROM scheduling s
        INNER JOIN user u ON s.user = u.id
        INNER JOIN user p ON s.provider = p.id
        INNER JOIN provider_data pd ON p.provider_data = pd.id
        INNER JOIN scheduling_status ss ON s.status = ss.id
        WHERE s.datetime BETWEEN :date_min AND :date_max
        AND pd.franchisee = :franchisee");
        $scheduling->execute(array(
            "franchisee" => $this->getUser()->getId(),
            "date_min" => $date_min ? $date_min->format("Y-m-d") : NULL,
            "date_max" => $date_max ? $date_max->format("Y-m-d") : NULL
        ));

        return $this->render("BlzBundle:Franchisee:stats-scheduling.html.twig", array(
            "scheduling" => $scheduling
        ));
    }

    /**
     * @Route("/estatisticas/movimentacoes/", name="StatsFinanceFranchisee")
     */
    public function statsFinanceAction(Request $request)
    {
        if(empty($this->getUser()->getPrivacyAccepted())){
            return $this->redirectToRoute("PrivacyFranchisee");
        }

        $em = $this->getDoctrine()->getManager();

        $dateMin = $request->request->get("date_min");
        $dateMax = $request->request->get("date_max");

        $date_min = (new \DateTime)->createFromFormat("d/m/Y", $dateMin);
        $date_max = (new \DateTime)->createFromFormat("d/m/Y", $dateMax);

        $finances = $em->getConnection()->prepare("SELECT p.name service, pd.name provider, pd.cpf, pd.cnpj, SUM(a.amount) amount, SUM(st.amount) franchisee_tax, SUM(ft.amount) system_tax FROM scheduling_service s
        INNER JOIN scheduling sc ON s.scheduling = sc.id
        INNER JOIN provider_service p ON s.service = p.id
        INNER JOIN user u ON p.user = u.id
        INNER JOIN provider_data pd ON u.provider_data = pd.id
        INNER JOIN finance a ON sc.amount = a.id
        INNER JOIN finance st ON sc.system_tax = st.id
        INNER JOIN finance ft ON sc.franchisee_tax = ft.id
        WHERE sc.datetime BETWEEN :date_min AND :date_max
        AND sc.status = 3
        AND pd.franchisee = :franchisee
        GROUP BY s.service");
        $finances->execute(array(
            "franchisee" => $this->getUser()->getId(),
            "date_min" => $date_min ? $date_min->format("Y-m-d") : NULL,
            "date_max" => $date_max ? $date_max->format("Y-m-d") : NULL
        ));

        return $this->render("BlzBundle:Franchisee:stats-finance.html.twig", array(
            "date_min" => $dateMin,
            "date_max" => $dateMax,
            "finances" => $finances
        ));
    }

    /**
     * @Route("/estatisticas/preco-medio/", name="StatsAvgPriceFranchisee")
     */
    public function statsAvgPriceAction(Request $request)
    {
        if(empty($this->getUser()->getPrivacyAccepted())){
            return $this->redirectToRoute("PrivacyFranchisee");
        }
        
        $em = $this->getDoctrine()->getManager();

        $dateMin = $request->request->get("date_min");
        $dateMax = $request->request->get("date_max");

        $date_min = (new \DateTime)->createFromFormat("d/m/Y", $dateMin);
        $date_max = (new \DateTime)->createFromFormat("d/m/Y", $dateMax);

        $services = $em->getConnection()->prepare("SELECT p.name service, pd.name provider, AVG(s.price) price_avg FROM scheduling_service s
        INNER JOIN scheduling sc ON s.scheduling = sc.id
        INNER JOIN provider_service p ON s.service = p.id
        INNER JOIN user u ON p.user = u.id
        INNER JOIN provider_data pd ON u.provider_data = pd.id
        WHERE sc.datetime BETWEEN :date_min AND :date_max
        AND pd.franchisee = :franchisee
        GROUP BY s.service");
        $services->execute(array(
            "franchisee" => $this->getUser()->getId(),
            "date_min" => $date_min ? $date_min->format("Y-m-d") : NULL,
            "date_max" => $date_max ? $date_max->format("Y-m-d") : NULL
        ));

        return $this->render("BlzBundle:Franchisee:stats-avg-price.html.twig", array(
            "date_min" => $dateMin,
            "date_max" => $dateMax,
            "services" => $services
        ));
    }

    /**
     * @Route("/estatisticas/mais-agendados/", name="StatsTopSchedulingFranchisee")
     */
    public function statsTopSchedulingAction(Request $request)
    {
        if(empty($this->getUser()->getPrivacyAccepted())){
            return $this->redirectToRoute("PrivacyFranchisee");
        }

        $em = $this->getDoctrine()->getManager();

        $dateMin = $request->request->get("date_min");
        $dateMax = $request->request->get("date_max");

        $date_min = (new \DateTime)->createFromFormat("d/m/Y", $dateMin);
        $date_max = (new \DateTime)->createFromFormat("d/m/Y", $dateMax);

        $services = $em->getConnection()->prepare("SELECT p.name service, pd.name provider, COUNT(s.id) count_service FROM scheduling_service s
        INNER JOIN scheduling sc ON s.scheduling = sc.id
        INNER JOIN provider_service p ON s.service = p.id
        INNER JOIN user u ON p.user = u.id
        INNER JOIN provider_data pd ON u.provider_data = pd.id
        WHERE sc.datetime BETWEEN :date_min AND :date_max
        AND pd.franchisee = :franchisee
        GROUP BY s.service
        ORDER BY count_service DESC");
        $services->execute(array(
            "franchisee" => $this->getUser()->getId(),
            "date_min" => $date_min ? $date_min->format("Y-m-d") : NULL,
            "date_max" => $date_max ? $date_max->format("Y-m-d") : NULL
        ));

        return $this->render("BlzBundle:Franchisee:stats-top-scheduling.html.twig", array(
            "date_min" => $dateMin,
            "date_max" => $dateMax,
            "services" => $services
        ));
    }

    /**
     * @Route("/privacidade/", name="PrivacyFranchisee")
     */
    public function privacyFranchiseeAction()
    {
        $em = $this->getDoctrine()->getManager();

        $franchiseePrivacy = $em->getRepository("BlzBundle:Configuration")->findOneByKey("franchisee_privacy");

        return $this->render("BlzBundle:Franchisee:privacy.html.twig", array(
            "franchiseePrivacy" => $franchiseePrivacy
        ));
    }

    /**
     * @Route("/accept-privacy/")
     * @Method("POST")
     */
    public function acceptPrivacyAction()
    {
        $em = $this->getDoctrine()->getManager();

        try{
            $this->getUser()->setPrivacyAccepted(true);

            $em->flush();

            return new Response("success");
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            return new Response($message, $status);
        }
    }
}
