<?php

namespace BlzBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use BlzBundle\Entity\ProviderService;
use BlzBundle\Entity\AvailableDate;
use BlzBundle\Entity\AvailableTime;
use BlzBundle\Entity\Ad;
use BlzBundle\Entity\ProviderPayment;
use BlzBundle\Entity\Chat;
use BlzBundle\Entity\Finance;
use BlzBundle\Entity\UserPoint;
use BlzBundle\Entity\ProviderServicePrice;

/**
 * @Route("/prestador-servico")
 */
class ProviderController extends Controller
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

    public function checkProvider()
    {
        $data = $this->getUser()->getProviderData();
        $address = $data->getAddress();
        
        if(empty($this->getUser()->getPrivacyAccepted()) || (empty($data->getCpf()) && empty($data->getCnpj())) || (empty($data->getPhone()) && empty($data->getCelphone())) || empty($data->getBirth()) || empty($address->getCep()) || empty($address->getStreet()) || empty($address->getNeighborhood()) || empty($address->getNumber()) || empty($address->getCity())){
            return false;
        }

        return true;
    }

    /**
     * @Route("/", name="IndexProvider")
     */
    public function indexAction()
    {
        if(!$this->checkProvider()){
            return $this->redirectToRoute("Profile");
        }

        $em = $this->getDoctrine()->getManager();

        $amount = $em->getConnection()->prepare("SELECT SUM(a.amount) amount,  SUM(st.amount) + SUM(ft.amount) franchisee_tax FROM scheduling sc
        INNER JOIN finance a ON sc.amount = a.id
        INNER JOIN finance st ON sc.system_tax = st.id
        INNER JOIN finance ft ON sc.franchisee_tax = ft.id
        WHERE sc.status = 3 AND sc.provider = ?");
        $amount->execute(array($this->getUser()->getId()));
        $amount = $amount->fetch();

        return $this->render('BlzBundle:Provider:index.html.twig', array(
            "amount" => $amount["amount"],
            "franchiseeTax" => $amount["franchisee_tax"]
        ));
    }
    
    /**
     * @Route("/login", name="LoginProvider")
     */
    public function loginAction()
    {
        $authUtils = $this->get("security.authentication_utils");
        
        $error = $authUtils->getLastAuthenticationError();

        $lastUsername = $authUtils->getLastUsername();

        return $this->render("BlzBundle:Provider:login.html.twig", array(
            "last_username" => $lastUsername,
            "error" => $error
        ));
    }

    /**
     * @Route("/servico/criar/", name="CreateService")
     */
    public function createServicePageAction()
    {
        if(!$this->checkProvider()){
            return $this->redirectToRoute("Profile");
        }

        $em = $this->getDoctrine()->getManager();

        $specialty = $em->createQueryBuilder()
        ->select("s")
        ->from("BlzBundle:Specialty", "s")
        ->join("BlzBundle:Category", "c", "WITH", "s.category = c")
        ->where("s.active = true")
        ->andWhere("c.active = true");
        $specialty = $specialty->getQuery()->getResult();

        $providerPayment = $em->getRepository("BlzBundle:ProviderPayment")->findByProvider($this->getUser());

        return $this->render("BlzBundle:Provider:service-create.html.twig", array(
            "specialty" => $specialty,
            "provider_payment" => $providerPayment,
            "times" => $this->serviceTimes
        ));
    }

    /**
     * @Route("/service/create/")
     * @Method("POST")
     */
    public function creatServiceAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $name = $request->request->get("name");
        $description = $request->request->get("description");
        $time = $request->request->get("time");
        $specialty = $request->request->get("specialty");
        $priority = $request->request->get("priority");

        try{
            if(empty($name) || empty($description) || $time == "" || empty($specialty)){
                throw new \Exception("Dados incompletos", 500);
            }

            $servicesActive = $em->getConnection()->prepare("SELECT COUNT(id) total FROM provider_service WHERE active = true AND deleted = false AND user = :user");
            $servicesActive->execute(array(
                "user" => $this->getUser()->getId()
            ));
            $servicesActive = $servicesActive->fetch();

            $plan = $this->getUser()->getProviderData()->getPlan();
            
            if($plan->getNumberOfServices() <= $servicesActive["total"]){
                throw new \Exception("O plano " . $plan->getName() . " não permite mais serviços. (" . $servicesActive["total"] . "/" . $plan->getNumberOfServices() . ")", 500);
            }

            $specialty = $em->getRepository("BlzBundle:Specialty")->findOneById($specialty);

            if(empty($specialty)){
                throw new \Exception("Especialidade não encontrada", 404);
            }

            $providerPayment = $em->getRepository("BlzBundle:ProviderPayment")->findByProvider($this->getUser());

            if(empty($providerPayment)){
                throw new \Exception("É necessário inserir seus métodos de pagamento", 500);
            }

            $service = new ProviderService;
            $service->setName($name);
            $service->setDescription($description);
            $service->setTime($time);
            $service->setUser($this->getUser());
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

            return new Response("success");
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            return new Response($message, $status);
        }
    }

    /**
     * @Route("/servicos/", name="ListService")
     */
    public function listServiceAction()
    {
        if(!$this->checkProvider()){
            return $this->redirectToRoute("Profile");
        }

        $em = $this->getDoctrine()->getManager();

        $service = $em->getRepository("BlzBundle:ProviderService")->findBy(array(
            "deleted" => false,
            "user" => $this->getUser()
        ), array(
            "priority" => "ASC"
        ));

        return $this->render("BlzBundle:Provider:service-list.html.twig", array(
            "service" => $service
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

            $service->setDeleted(true);

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
        if(!$this->checkProvider()){
            return $this->redirectToRoute("Profile");
        }
        
        $em = $this->getDoctrine()->getManager();

        $service = $em->getRepository("BlzBundle:ProviderService")->findOneBy(array(
            "id" => $id,
            "deleted" => false
        ));

        $specialty = $em->createQueryBuilder()
        ->select("s")
        ->from("BlzBundle:Specialty", "s")
        ->join("BlzBundle:Category", "c", "WITH", "s.category = c")
        ->where("s.active = true")
        ->andWhere("c.active = true");
        $specialty = $specialty->getQuery()->getResult();

        $providerServicePrice = $em->getConnection()->prepare("SELECT pp.id, pm.name payment_method, psp.price FROM provider_payment pp
        INNER JOIN payment_method pm ON pp.payment_method = pm.id
        LEFT JOIN provider_service_price psp ON psp.provider_payment = pp.id AND psp.provider_service = ?
        WHERE pp.provider = ?");
        $providerServicePrice->execute(array($service->getId(), $this->getUser()->getId()));
        $providerServicePrice = $providerServicePrice->fetchAll();

        return $this->render("BlzBundle:Provider:service-edit.html.twig", array(
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

            $service = $em->getRepository("BlzBundle:ProviderService")->findOneBy(array(
                "id" => $id,
                "user" => $this->getUser()
            ));

            if(empty($service)){
                throw new \Exception("Serviço não encontrado", 404);
            }

            $specialty = $em->getRepository("BlzBundle:Specialty")->findOneById($specialty);

            if(empty($specialty)){
                throw new \Exception("Especialidade não encontrada", 404);
            }

            $providerPayment = $em->getRepository("BlzBundle:ProviderPayment")->findByProvider($this->getUser());

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

            return new Response("success");
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            return new Response($message, $status);
        }
    }

    /**
     * @Route("/perfil/", name="Profile")
     */
    public function editProviderPageAction()
    {
        if(empty($this->getUser()->getPrivacyAccepted())){
            return $this->redirectToRoute("PrivacyProvider");
        }

        $em = $this->getDoctrine()->getManager();

        $states = $em->getRepository("BlzBundle:State")->findAll();

        if(empty($this->getUser()->getProviderData()->getAddress()->getCity())){
            $sp = $em->getRepository("BlzBundle:State")->findOneById(26);
            $city = $em->getRepository("BlzBundle:City")->findByState($sp);
        }
        else{
            $city = $em->getRepository("BlzBundle:City")->findByState($this->getUser()->getProviderData()->getAddress()->getCity()->getState());
        }

        return $this->render("BlzBundle:Provider:profile.html.twig", array(
            "states" => $states,
            "city" => $city,
            "checkProvider" => $this->checkProvider()
        ));
    }

    /**
     * @Route("/edit/")
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

            $city = $em->getRepository("BlzBundle:City")->findOneById($city);

            if(empty($city)){
                throw new \Exception("Cidade não encontrada", 404);
            }

            $checkUser = $em->getRepository("BlzBundle:User")->findOneByEmail($email);

            if(!empty($checkUser) && $email != $this->getUser()->getEmail()){
                throw new \Exception("Já existe um usuário com este email", 500);
            }

            $data = $this->getUser()->getProviderData();
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

            $this->getUser()->setName($name);
            $this->getUser()->setEmail($email);
            
            if(!empty($password)){
                $this->getUser()->setPassword(md5($password));
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
     * @Route("/servicos-agendados/", name="SchedulingProvider")
     */
    public function schedulingAction()
    {
        if(!$this->checkProvider()){
            return $this->redirectToRoute("Profile");
        }
        
        $em = $this->getDoctrine()->getManager();

        $waiting = $em->getRepository("BlzBundle:Scheduling")->findBy(array(
            "provider" => $this->getUser(),
            "status" => $em->getRepository("BlzBundle:SchedulingStatus")->findOneById(1)
        ), array(
            "id" => "DESC"
        ));

        $canceled = $em->getRepository("BlzBundle:Scheduling")->findBy(array(
            "provider" => $this->getUser(),
            "status" => $em->getRepository("BlzBundle:SchedulingStatus")->findOneById(2)
        ), array(
            "id" => "DESC"
        ));

        $finalized = $em->getRepository("BlzBundle:Scheduling")->findBy(array(
            "provider" => $this->getUser(),
            "status" => $em->getRepository("BlzBundle:SchedulingStatus")->findOneById(3)
        ), array(
            "id" => "DESC"
        ));

        return $this->render("BlzBundle:Provider:scheduling.html.twig", array(
            "waiting" => $waiting,
            "canceled" => $canceled,
            "finalized" => $finalized
        ));
    }

    /**
     * @Route("/agendamento/{id}/")
     */
    public function schedulingDetailsAction($id)
    {
        if(!$this->checkProvider()){
            return $this->redirectToRoute("Profile");
        }

        $em = $this->getDoctrine()->getManager();
        
        $scheduling = $em->getRepository("BlzBundle:Scheduling")->findOneBy(array(
            "id" => $id,
            "provider" => $this->getUser()
        ));
        $schedulingServices = $em->getRepository("BlzBundle:SchedulingService")->findByScheduling($scheduling);

        $scheduling->totalAmount = 0;
        for($i = 0; $i < count($schedulingServices); $i = $i + 1){
            $scheduling->totalAmount = $scheduling->totalAmount + $schedulingServices[$i]->getPrice();
        }

        return $this->render("BlzBundle:Provider:scheduling-details.html.twig", array(
            "scheduling" => $scheduling,
            "scheduling_services" => $schedulingServices
        ));
    }

    /**
     * @Route("/scheduling/cancel/")
     * @Method("POST")
     */
    public function schedulingCancelAction(Request $request)
    {
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

            if($scheduling->getProvider()->getId() != $this->getUser()->getId()){
                throw new \Exception("Você não tem permissão para isso", 403);
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
     * @Route("/scheduling/finalize/")
     * @Method("POST")
     */
    public function schedulingFinalizeAction(Request $request)
    {
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

            if($scheduling->getProvider()->getId() != $this->getUser()->getId()){
                throw new \Exception("Você não tem permissão para isso", 403);
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
     * @Route("/agenda/", name="OrganizeSchedule")
     */
    public function schedulePageAction()
    {
        if(!$this->checkProvider()){
            return $this->redirectToRoute("Profile");
        }
        
        $em = $this->getDoctrine()->getManager();

        $availableDate = $em->getRepository("BlzBundle:AvailableDate")->findByUser($this->getUser());

        $dates = array();

        for($i = 0; $i < count($availableDate); $i = $i + 1){
            $dates[] = $availableDate[$i]->getDate()->format("Y-m-d");
        }

        return $this->render("BlzBundle:Provider:schedule.html.twig", array(
            "dates" => $dates,
            "times" => $this->times
        ));
    }

    /**
     * @Route("/schedule/")
     */
    public function scheduleAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $date = $request->request->get("date");
        $time = $request->request->get("time");

        try{
            if(empty($date)){
                throw new \Exception("Dados incompletos", 500);
            }

            $availableDate = $em->getRepository("BlzBundle:AvailableDate")->findOneBy(array(
                "date" => new \DateTime($date),
                "user" => $this->getUser()
            ));

            if(empty($availableDate)){
                $availableDate = new AvailableDate;
                $availableDate->setDate(new \DateTime($date));
                $availableDate->setUser($this->getUser());

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
     * @Route("/schedule/auto/")
     */
    public function scheduleAutoAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $dateMin = $request->request->get("date_min");
        $dateMax = $request->request->get("date_max");
        $dayMin = $request->request->get("day_min");
        $dayMax = $request->request->get("day_max");
        $timeMin = $request->request->get("time_min");
        $timeMax = $request->request->get("time_max");

        try{
            if(empty($dateMin) || empty($dateMax) || empty($dayMin) || empty($dayMax)){
                throw new \Exception("Dados incompletos", 500);
            }

            $dateMin = (new \DateTime)->createFromFormat("d/m/Y", $dateMin);
            $dateMax = (new \DateTime)->createFromFormat("d/m/Y", $dateMax);

            if($dateMin >= $dateMax){
                throw new \Exception("A data mínima deve ser menor que a máxima", 500);
            }

            if($dayMin >= $dayMax){
                throw new \Exception("O dia mínimo deve ser menor que o máximo", 500);
            }

            if($timeMin >= $timeMax){
                throw new \Exception("A hora mínima deve ser menor que a máxima", 500);
            }

            while($dateMin <= $dateMax){
                $dayWeek = $dateMin->format("N");

                $availableDate = $em->getRepository("BlzBundle:AvailableDate")->findOneBy(array(
                    "date" => $dateMin,
                    "user" => $this->getUser()
                ));

                if($dayWeek >= $dayMin && $dayWeek <= $dayMax){
                    if(empty($availableDate)){
                        $availableDate = new AvailableDate;
                        $availableDate->setDate($dateMin);
                        $availableDate->setUser($this->getUser());
        
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
     * @Route("/schedule/date/")
     */
    public function getDateScheduleAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $date = $request->request->get("date");

        try{
            if(empty($date)){
                throw new \Exception("Dados incompletos", 500);
            }

            $availableDate = $em->getRepository("BlzBundle:AvailableDate")->findOneBy(array(
                "date" => new \DateTime($date),
                "user" => $this->getUser()
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
     * @Route("/anuncio/criar/", name="CreateAdProvider")
     */
    public function createAdPageAction()
    {
        if(!$this->checkProvider()){
            return $this->redirectToRoute("Profile");
        }

        $em = $this->getDoctrine()->getManager();

        return $this->render("BlzBundle:Provider:ad-create.html.twig", array(
            "lastDayOfThisMonth" => new \DateTime("last day of this month")
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
        $photo = $request->request->get("photo");

        try{
            if(empty($name) || empty($description) || empty($validity)){
                throw new \Exception("Dados incompletos", 500);
            }

            $adsActive = $em->getConnection()->prepare("SELECT COUNT(id) total FROM ad WHERE active = true AND DATE_FORMAT(CURDATE() ,'%Y-%m-01') AND provider = :provider");
            $adsActive->execute(array(
                "provider" => $this->getUser()->getId()
            ));
            $adsActive = $adsActive->fetch();

            $plan = $this->getUser()->getProviderData()->getPlan();
            
            if($plan->getNumberOfAds() <= $adsActive["total"]){
                throw new \Exception("O plano " . $plan->getName() . " não permite mais serviços. (" . $adsActive["total"] . "/" . $plan->getNumberOfAds() . ")", 500);
            }

            $photo = json_decode($photo);

            if(count($photo) == 0){
                throw new \Exception("É necessário uma imagem para o anúncio", 500);
            }

            $ad = new Ad;
            $ad->setName($name);
            $ad->setDescription($description);
            $ad->setValidity((new \Datetime)->createFromFormat("d/m/Y", $validity));
            $ad->setProvider($this->getUser());

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
     * @Route("/anuncios/", name="ListAdProvider")
     */
    public function listAdAction()
    {
        if(!$this->checkProvider()){
            return $this->redirectToRoute("Profile");
        }

        $em = $this->getDoctrine()->getManager();

        $ad = $em->getRepository("BlzBundle:Ad")->findBy(array(
            "active" => true,
            "provider" => $this->getUser()
        ), array(
            "id" => "DESC"
        ));

        return $this->render("BlzBundle:Provider:ad-list.html.twig", array(
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
                "active" => true,
                "provider" => $this->getUser()
            ));

            if(empty($ad)){
                throw new \Exception("Anúncio não encontrado", 404);
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
        if(!$this->checkProvider()){
            return $this->redirectToRoute("Profile");
        }

        $em = $this->getDoctrine()->getManager();

        $ad = $em->getRepository("BlzBundle:Ad")->findOneBy(array(
            "id" => $id,
            "active" => true,
            "provider" => $this->getUser()
        ));

        return $this->render("BlzBundle:Provider:ad-edit.html.twig", array(
            "ad" => $ad
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
            if(empty($name) || empty($description) || empty($validity) || empty($id)){
                throw new \Exception("Dados incompletos", 500);
            }

            $ad = $em->getRepository("BlzBundle:Ad")->findOneById($id);

            if(empty($ad)){
                throw new \Exception("Anúncio não encontrado", 404);
            }

            $ad->setName($name);
            $ad->setDescription($description);
            $ad->setValidity((new \Datetime)->createFromFormat("d/m/Y", $validity));

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
     * @Route("/metodos-pagamento/", name="PaymentMethods")
     */
    public function paymentMethodsPageAction()
    {
        if(!$this->checkProvider()){
            return $this->redirectToRoute("Profile");
        }

        $em = $this->getDoctrine()->getManager();

        $providerPaymentMethods = $em->getConnection()->prepare("SELECT payment_method FROM provider_payment WHERE provider = ?");
        $providerPaymentMethods->execute(array($this->getUser()->getId()));
        $providerPaymentMethods = $providerPaymentMethods->fetchAll();

        $paymentList = array();
        for($i = 0; $i < count($providerPaymentMethods); $i = $i + 1){
            $paymentList[] = $providerPaymentMethods[$i]["payment_method"];
        }

        $paymentMethod = $em->getRepository("BlzBundle:PaymentMethod")->findByActive(true);

        for($i = 0; $i < count($paymentMethod); $i = $i + 1){
            $paymentMethod[$i]->checked = in_array($paymentMethod[$i]->getId(), $paymentList);
        }

        return $this->render("BlzBundle:Provider:payment-methods.html.twig", array(
            "payment_method" => $paymentMethod
        ));
    }

    /**
     * @Route("/payment-methods/")
     * @Method("POST")
     */
    public function paymentMethodsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $paymentMethods = $request->request->get("payment_method") ? $request->request->get("payment_method") : array();

        try{
            for($i = 0; $i < count($paymentMethods); $i = $i + 1){
                $paymentMethod = $em->getRepository("BlzBundle:PaymentMethod")->findOneById($paymentMethods[$i]);

                $checkProviderPayment = $em->getRepository("BlzBundle:ProviderPayment")->findOneBy(array(
                    "provider" => $this->getUser(),
                    "paymentMethod" => $paymentMethod
                ));
                
                if(empty($checkProviderPayment)){
                    $providerPayment = new ProviderPayment;
                    $providerPayment->setProvider($this->getUser());
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
     * @Route("/push/")
     * @Method("POST")
     */
    public function setPushAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $push = $request->request->get("push");

        try{
            if(empty($push)){
                throw new \Exception("Dados incompletos", 500);
            }

            $this->getUser()->getProviderData()->setPushWeb($push);

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
     * @Route("/chats/", name="Chats")
     */
    public function chatListAction()
    {
        if(!$this->checkProvider()){
            return $this->redirectToRoute("Profile");
        }

        $em = $this->getDoctrine()->getManager();

        $chat = $em->createQueryBuilder()
        ->select("c")
        ->from("BlzBundle:ChatRoom", "c")
        ->where("c.active = true")
        ->andWhere("c.lastMessage IS NOT NULL")
        ->andWhere("c.lastDatetime IS NOT NULL")       
        ->andWhere("c.provider = :provider")
        ->setParameter("provider", $this->getuser());
        $chat = $chat->getQuery()->getResult();

        return $this->render("BlzBundle:Provider:chat-list.html.twig", array(
            "chat" => $chat
        ));
    }

    /**
     * @Route("/chat/add/")
     * @Method("POST")
     */
    public function chatAddAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $chatRoom = $request->request->get("chat_room");
        $message = $request->request->get("message");
        $to = $request->request->get("to_user");

        try{
            if(empty($chatRoom) || empty($message) || empty($to)){
                throw new \Exception("Dados incompletos", 500);
            }
            
            $chatRoom = $em->getRepository("BlzBundle:ChatRoom")->findOneBy(array(
                "id" => $chatRoom,
                "active" => true
            ));

            if(empty($chatRoom)){
                throw new \Exception("Conversa não encontrada", 404);
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
            $chat->setFromUser($this->getUser());
            $chat->setToUser($to);
            $chat->setChatRoom($chatRoom);

            $em->persist($chat);

            $chatRoom->setLastMessage($message);
            $chatRoom->setLastDatetime(new \DateTime);

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
     * @Route("/chat/{id}/")
     */
    public function chatAction($id)
    {
        if(!$this->checkProvider()){
            return $this->redirectToRoute("Profile");
        }

        $em = $this->getDoctrine()->getManager();

        $chat = array();
        $chatRoom = $em->getRepository("BlzBundle:ChatRoom")->findOneById($id);

        if(!empty($chatRoom)){
            if($chatRoom->getProvider()->getId() != $this->getUser()->getId()){
                $chatRoom = NULL;
            }
            else{
                $chat = $em->getRepository("BlzBundle:Chat")->findByChatRoom($id);
            }
        }

        return $this->render("BlzBundle:Provider:chat.html.twig", array(
            "chat" => $chat,
            "chat_room" => $chatRoom
        ));
    }

    /**
     * @Route("/financeiro/", name="Finances")
     */
    public function financesAction()
    {
        if(!$this->checkProvider()){
            return $this->redirectToRoute("Profile");
        }

        $em = $this->getDoctrine()->getManager();

        $amount = $em->getConnection()->prepare("SELECT SUM(a.amount) amount, SUM(st.amount) + SUM(ft.amount) franchisee_tax FROM scheduling sc
        INNER JOIN finance a ON sc.amount = a.id
        INNER JOIN finance st ON sc.system_tax = st.id
        INNER JOIN finance ft ON sc.franchisee_tax = ft.id
        WHERE sc.status = 3 AND sc.provider = ?");
        $amount->execute(array($this->getUser()->getId()));
        $amount = $amount->fetch();

        $finances = $em->getRepository("BlzBundle:Scheduling")->findBy(array(
            "provider" => $this->getUser(),
            "status" => $em->getRepository("BlzBundle:SchedulingStatus")->findOneById(3)
        ), array(
            "id" => "DESC"
        ));

        return $this->render("BlzBundle:Provider:finances.html.twig", array(
            "finances" => $finances,
            "amount" => $amount["amount"],
            "franchiseeTax" => $amount["franchisee_tax"]
        ));
    }

    /**
     * @Route("/privacidade/", name="PrivacyProvider")
     */
    public function privacyProviderAction()
    {
        $em = $this->getDoctrine()->getManager();

        $providerPrivacy = $em->getRepository("BlzBundle:Configuration")->findOneByKey("provider_privacy");

        return $this->render("BlzBundle:Provider:privacy.html.twig", array(
            "providerPrivacy" => $providerPrivacy
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

    /**
     * @Route("/prestadores-servico-salao/", name="ListProviderSaloon")
     */
    public function listProviderAction()
    {
        if(!$this->checkProvider()){
            return $this->redirectToRoute("Profile");
        }

        $em = $this->getDoctrine()->getManager();

        $provider = $em->createQueryBuilder()
        ->select("u")
        ->from("BlzBundle:User", "u")
        ->join("BlzBundle:ProviderData", "d", "WITH", "u.providerData = d")
        ->where("u.active = true")
        ->andWhere("u != :user")
        ->andWhere("u.type = :type")
        ->andWhere("d.cpf = :cpf AND d.cnpj = :cnpj")
        ->orderBy("u.id", "DESC")
        ->setParameter("user", $this->getUser())
        ->setParameter("type", $em->getRepository("BlzBundle:UserType")->findOneById(3))
        ->setParameter("cpf", $this->getUser()->getProviderData()->getCpf())
        ->setParameter("cnpj", $this->getUser()->getProviderData()->getCnpj());
        $provider = $provider->getQuery()->getResult();

        return $this->render("BlzBundle:Provider:saloon-provider-list.html.twig", array(
            "provider" => $provider
        ));
    }

    /**
     * @Route("/prestadores-servico-salao/{id}/agenda/", name="OrganizeScheduleSaloon")
     */
    public function scheduleSaloonPageAction($id)
    {
        if(!$this->checkProvider()){
            return $this->redirectToRoute("Profile");
        }
        
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository("BlzBundle:User")->findOneById($id);

        if(!empty($user)){
            if($user->getProviderData()->getCpf() != $this->getUser()->getProviderData()->getCpf() || $user->getProviderData()->getCnpj() != $this->getUser()->getProviderData()->getCnpj()){
                $user = NULL;
            }
        }

        $availableDate = $em->getRepository("BlzBundle:AvailableDate")->findByUser($user);

        $dates = array();

        for($i = 0; $i < count($availableDate); $i = $i + 1){
            $dates[] = $availableDate[$i]->getDate()->format("Y-m-d");
        }

        return $this->render("BlzBundle:Provider:saloon-schedule.html.twig", array(
            "dates" => $dates,
            "times" => $this->times,
            "provider" => $user
        ));
    }

    /**
     * @Route("/saloon/schedule/")
     */
    public function scheduleSaloonAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $user = $request->request->get("user");
        $date = $request->request->get("date");
        $time = $request->request->get("time");

        try{
            if(empty($date)){
                throw new \Exception("Dados incompletos", 500);
            }

            $user = $em->getRepository("BlzBundle:User")->findOneById($user);

            if(empty($user)){
                throw new \Exception("Profissional não encontrado", 404);
            }

            if($user->getProviderData()->getCpf() != $this->getUser()->getProviderData()->getCpf() || $user->getProviderData()->getCnpj() != $this->getUser()->getProviderData()->getCnpj()){
                throw new \Exception("Você não tem permissão para isso", 403);
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
     * @Route("/saloon/schedule/auto/")
     */
    public function scheduleAutoSaloonAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $user = $request->request->get("user");
        $dateMin = $request->request->get("date_min");
        $dateMax = $request->request->get("date_max");
        $dayMin = $request->request->get("day_min");
        $dayMax = $request->request->get("day_max");
        $timeMin = $request->request->get("time_min");
        $timeMax = $request->request->get("time_max");

        try{
            if(empty($dateMin) || empty($dateMax) || empty($dayMin) || empty($dayMax)){
                throw new \Exception("Dados incompletos", 500);
            }

            $user = $em->getRepository("BlzBundle:User")->findOneById($user);

            if(empty($user)){
                throw new \Exception("Profissional não encontrado", 404);
            }

            if($user->getProviderData()->getCpf() != $this->getUser()->getProviderData()->getCpf() || $user->getProviderData()->getCnpj() != $this->getUser()->getProviderData()->getCnpj()){
                throw new \Exception("Você não tem permissão para isso", 403);
            }

            $dateMin = (new \DateTime)->createFromFormat("d/m/Y", $dateMin);
            $dateMax = (new \DateTime)->createFromFormat("d/m/Y", $dateMax);

            if($dateMin >= $dateMax){
                throw new \Exception("A data mínima deve ser menor que a máxima", 500);
            }

            if($dayMin >= $dayMax){
                throw new \Exception("O dia mínimo deve ser menor que o máximo", 500);
            }

            if($timeMin >= $timeMax){
                throw new \Exception("A hora mínima deve ser menor que a máxima", 500);
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
     * @Route("/saloon/schedule/date/")
     */
    public function getDateScheduleSaloonAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $request->request->get("user");
        $date = $request->request->get("date");

        try{
            if(empty($date)){
                throw new \Exception("Dados incompletos", 500);
            }

            $user = $em->getRepository("BlzBundle:User")->findOneById($user);

            if(empty($user)){
                throw new \Exception("Profissional não encontrado", 404);
            }

            if($user->getProviderData()->getCpf() != $this->getUser()->getProviderData()->getCpf() || $user->getProviderData()->getCnpj() != $this->getUser()->getProviderData()->getCnpj()){
                throw new \Exception("Você não tem permissão para isso", 403);
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
}
