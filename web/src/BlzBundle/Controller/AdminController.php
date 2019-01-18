<?php

namespace BlzBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use BlzBundle\Entity\User;
use BlzBundle\Entity\ProviderData;
use BlzBundle\Entity\FranchiseeData;
use BlzBundle\Entity\Category;
use BlzBundle\Entity\Specialty;
use BlzBundle\Entity\Address;
use BlzBundle\Entity\Plan;
use BlzBundle\Entity\Ad;
use BlzBundle\Entity\PaymentMethod;
use BlzBundle\Entity\Award;
use BlzBundle\Entity\UserPoint;

/**
 * @Route("/admin")
 */
class AdminController extends Controller
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
     * @Route("/", name="IndexAdmin")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $provider = $em->getConnection()->prepare("SELECT COUNT(id) total FROM user WHERE active = true AND type = 3");
        $provider->execute();
        $provider = $provider->fetch();

        $client = $em->getConnection()->prepare("SELECT COUNT(id) total FROM user WHERE active = true AND type = 4");
        $client->execute();
        $client = $client->fetch();

        $admin = $em->getConnection()->prepare("SELECT COUNT(id) total FROM user WHERE active = true AND type = 1");
        $admin->execute();
        $admin = $admin->fetch();

        $providers = $em->getConnection()->prepare("SELECT u.id, u.name, u.email, d.phone, d.celphone, c.name city, s.name state, fd.name franchisee FROM user u
        INNER JOIN provider_data d ON u.provider_data = d.id
        INNER JOIN address a ON d.address = a.id
        INNER JOIN city c ON a.city = c.id
        INNER JOIN state s ON c.state = s.id
        LEFT JOIN user f ON d.franchisee = f.id
        LEFT JOIN franchisee_data fd ON f.franchisee_data = fd.id
        WHERE u.active = true AND u.type = 3 AND u.datetime >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $providers->execute();
        $providers = $providers->fetchAll();

        return $this->render("BlzBundle:Admin:index.html.twig", array(
            "provider" => $provider["total"],
            "client" => $client["total"],
            "admin" => $admin["total"],
            "providers" => $providers
        ));
    }

    /**
     * @Route("/login", name="LoginAdmin")
     */
    public function loginAction()
    {
        $authUtils = $this->get("security.authentication_utils");
        
        $error = $authUtils->getLastAuthenticationError();

        $lastUsername = $authUtils->getLastUsername();

        return $this->render("BlzBundle:Admin:login.html.twig", array(
            "last_username" => $lastUsername,
            "error" => $error
        ));
    }

    /**
     * @Route("/criar/", name="CreateAdmin")
     */
    public function createAdminPageAction()
    {
        return $this->render("BlzBundle:Admin:admin-create.html.twig");
    }

    /**
     * @Route("/create/")
     * @Method("POST")
     */
    public function createAdminAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $name = $request->request->get("name");
        $email = $request->request->get("email");
        $password = $request->request->get("password");

        try{
            if(empty($name) || empty($email) || empty($password)){
                throw new \Exception("Dados incompletos", 500);
            }

            $checkUser = $em->getRepository("BlzBundle:User")->findOneByEmail($email);

            if(!empty($checkUser)){
                throw new \Exception("Já existe um usuário com este email", 500);
            }

            $admin = new User;
            $admin->setName($name);
            $admin->setEmail($email);
            $admin->setPassword(md5($password));
            $admin->setType($em->getRepository("BlzBundle:UserType")->findOneById(1));

            $em->persist($admin);
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
     * @Route("/administradores/", name="ListAdmin")
     */
    public function listAdminAction()
    {
        $em = $this->getDoctrine()->getManager();

        $admin = $em->getRepository("BlzBundle:User")->findBy(array(
            "active" => true,
            "type" => $em->getRepository("BlzBundle:UserType")->findOneById(1)
        ), array(
            "id" => "DESC"
        ));

        return $this->render("BlzBundle:Admin:admin-list.html.twig", array(
            "admin" => $admin
        ));
    }

    /**
     * @Route("/delete/")
     * @Method("POST")
     */
    public function deleteAdminAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");

        try{
            if(empty($id)){
                throw new \Exception("Dados incompletos", 500);
            }

            $admin = $em->getRepository("BlzBundle:User")->findOneBy(array(
                "id" => $id,
                "active" => true
            ));

            if(empty($admin)){
                throw new \Exception("Administrador não encontrado", 404);
            }

            $admin->setActive(false);

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
     * @Route("/administrador/{id}/")
     */
    public function editAdminPageAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $admin = $em->getRepository("BlzBundle:User")->findOneBy(array(
            "id" => $id,
            "active" => true,
            "type" => $em->getRepository("BlzBundle:UserType")->findOneById(1)
        ));

        return $this->render("BlzBundle:Admin:admin-edit.html.twig", array(
            "admin" => $admin
        ));
    }

    /**
     * @Route("/edit/")
     * @Method("POST")
     */
    public function editAdminAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");
        $name = $request->request->get("name");
        $email = $request->request->get("email");
        $password = $request->request->get("password");

        try{
            if(empty($id) || empty($name) || empty($email)){
                throw new \Exception("Dados incompletos", 500);
            }

            $admin = $em->getRepository("BlzBundle:User")->findOneBy(array(
                "id" => $id,
                "active" => true,
                "type" => $em->getRepository("BlzBundle:UserType")->findOneById(1)
            ));
            
            if(empty($admin)){
                throw new \Exception("Administrador não encontrado", 404);
            }

            $checkUser = $em->getRepository("BlzBundle:User")->findOneByEmail($email);

            if(!empty($checkUser) && $email != $admin->getEmail()){
                throw new \Exception("Já existe um usuário com este email", 500);
            }
            
            $admin->setName($name);
            $admin->setEmail($email);
            
            if(!empty($password)){
                $admin->setPassword(md5($password));
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
     * @Route("/franqueado/criar/", name="CreateFranchisee")
     */
    public function createFranchiseePageAction()
    {
        $em = $this->getDoctrine()->getManager();

        $sp = $em->getRepository("BlzBundle:State")->findOneById(26);
        $states = $em->getRepository("BlzBundle:State")->findAll();
        $city = $em->getRepository("BlzBundle:City")->findByState($sp);

        return $this->render("BlzBundle:Admin:franchisee-create.html.twig", array(
            "states" => $states,
            "city" => $city
        ));
    }

    /**
     * @Route("/franchisee/create/")
     * @Method("POST")
     */
    public function createFranchiseeAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $name = $request->request->get("name");
        $sponsorName = $request->request->get("sponsor_name");
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
        $bank = $request->request->get("bank");
        $agency = $request->request->get("agency");
        $account = $request->request->get("account");
        $nameBank = $request->request->get("name_bank");
        $cpfBank = $request->request->get("cpf_bank");
        $cnpjBank = $request->request->get("cnpj_bank");

        try{
            if(empty($name) || empty($sponsorName) || empty($birth) || empty($email) || empty($password) || empty($name) || empty($cep) || empty($street) || empty($neighborhood) || empty($number) || empty($city) || empty($bank) || empty($agency) || empty($account) || empty($nameBank)){
                throw new \Exception("Dados incompletos", 500);
            }

            if(empty($cpf) && empty($cnpj)){
                throw new \Exception("É necessário informar CPF ou CNPJ", 500);
            }

            if(empty($phone) && empty($celphone)){
                throw new \Exception("É necessário informar Telefone ou celular", 500);
            }

            if(empty($cpfBank) && empty($cnpjBank)){
                throw new \Exception("É necessário informar CPF ou CNPJ do favorecido", 500);
            }

            $city = $em->getRepository("BlzBundle:City")->findOneById($city);

            if(empty($city)){
                throw new \Exception("Cidade não encontrada", 404);
            }

            $userType = $em->getRepository("BlzBundle:UserType")->findOneById(2);

            if(empty($userType)){
                throw new \Exception("Tipo de usuário não encontrado", 404);
            }

            $checkUser = $em->getRepository("BlzBundle:User")->findOneByEmail($email);

            if(!empty($checkUser)){
                throw new \Exception("Já existe um usuário com este email", 500);
            }

            $address = new Address;
            $address->setCep(preg_replace("/([^0-9]+)/", "", $cep));
            $address->setStreet($street);
            $address->setNeighborhood($neighborhood);
            $address->setNumber($number);
            $address->setCity($city);

            $em->persist($address);

            $data = new FranchiseeData;
            $data->setName($sponsorName);
            $data->setBirth((new \Datetime)->createFromFormat("d/m/Y", $birth));
            $data->setCpf(preg_replace("/([^0-9]+)/", "", $cpf));
            $data->setCnpj(preg_replace("/([^0-9]+)/", "", $cnpj));
            $data->setPhone(preg_replace("/([^0-9]+)/", "", $phone));
            $data->setCelphone(preg_replace("/([^0-9]+)/", "", $celphone));
            $data->setBank($bank);
            $data->setAgency($agency);
            $data->setAccount($account);
            $data->setNameBank($nameBank);
            $data->setCpfBank(preg_replace("/([^0-9]+)/", "", $cpfBank));
            $data->setCnpjBank(preg_replace("/([^0-9]+)/", "", $cnpjBank));
            $data->setAddress($address);

            $em->persist($data);

            $user = new User;
            $user->setName($name);
            $user->setEmail($email);
            $user->setPassword(md5($password));
            $user->setType($userType);
            $user->setFranchiseeData($data);

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
     * @Route("/franqueados/", name="ListFranchisee")
     */
    public function listFranchiseeAction()
    {
        $em = $this->getDoctrine()->getManager();

        $franchisee = $em->getRepository("BlzBundle:User")->findBy(array(
            "type" => $em->getRepository("BlzBundle:UserType")->findOneById(2)
        ), array(
            "id" => "DESC"
        ));

        return $this->render("BlzBundle:Admin:franchisee-list.html.twig", array(
            "franchisee" => $franchisee
        ));
    }

    /**
     * @Route("/franchisee/toggle/")
     * @Method("POST")
     */
    public function toggleFranchiseeAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");

        try{
            if(empty($id)){
                throw new \Exception("Dados incompletos", 500);
            }

            $franchisee = $em->getRepository("BlzBundle:User")->findOneById($id);

            if(empty($franchisee)){
                throw new \Exception("Franqueado não encontrado", 404);
            }

            $franchisee->setActive(!$franchisee->getActive());

            $em->flush();

            return new Response($franchisee->getActive() ? 1 : 0);
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            return new Response($message, $status);
        }
    }

    /**
     * @Route("/franqueado/{id}/")
     */
    public function editFranchiseePageAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $franchisee = $em->getRepository("BlzBundle:User")->findOneBy(array(
            "id" => $id,
            "active" => true,
            "type" => $em->getRepository("BlzBundle:UserType")->findOneById(2)
        ));

        $states = $em->getRepository("BlzBundle:State")->findAll();
        $city = $em->getRepository("BlzBundle:City")->findByState($franchisee->getFranchiseeData()->getAddress()->getCity()->getState());

        return $this->render("BlzBundle:Admin:franchisee-edit.html.twig", array(
            "franchisee" => $franchisee,
            "states" => $states,
            "city" => $city
        ));
    }

    /**
     * @Route("/franchisee/edit/")
     * @Method("POST")
     */
    public function editFranchiseeAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $id = $request->request->get("id");
        $name = $request->request->get("name");
        $sponsorName = $request->request->get("sponsor_name");
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
        $bank = $request->request->get("bank");
        $agency = $request->request->get("agency");
        $account = $request->request->get("account");
        $nameBank = $request->request->get("name_bank");
        $cpfBank = $request->request->get("cpf_bank");
        $cnpjBank = $request->request->get("cnpj_bank");

        try{
            if(empty($id) || empty($name) || empty($sponsorName) || empty($birth) || empty($email) || empty($name) || empty($cep) || empty($street) || empty($neighborhood) || empty($number) || empty($city) || empty($bank) || empty($agency) || empty($account) || empty($nameBank)){
                throw new \Exception("Dados incompletos", 500);
            }

            if(empty($cpf) && empty($cnpj)){
                throw new \Exception("É necessário informar CPF ou CNPJ", 500);
            }

            if(empty($phone) && empty($celphone)){
                throw new \Exception("É necessário informar Telefone ou celular", 500);
            }

            if(empty($cpfBank) && empty($cnpjBank)){
                throw new \Exception("É necessário informar CPF ou CNPJ do favorecido", 500);
            }

            $city = $em->getRepository("BlzBundle:City")->findOneById($city);

            if(empty($city)){
                throw new \Exception("Cidade não encontrada", 404);
            }

            $user = $em->getRepository("BlzBundle:User")->findOneBy(array(
                "id" => $id,
                "active" => true,
                "type" => $em->getRepository("BlzBundle:UserType")->findOneById(2)
            ));

            if(empty($user)){
                throw new \Exception("Franqueado não encontrado", 404);
            }

            $checkUser = $em->getRepository("BlzBundle:User")->findOneByEmail($email);

            if(!empty($checkUser) && $email != $user->getEmail()){
                throw new \Exception("Já existe um usuário com este email", 500);
            }

            $data = $user->getFranchiseeData();
            $data->setName($sponsorName);
            $data->setBirth((new \Datetime)->createFromFormat("d/m/Y", $birth));
            $data->setCpf(preg_replace("/([^0-9]+)/", "", $cpf));
            $data->setCnpj(preg_replace("/([^0-9]+)/", "", $cnpj));
            $data->setPhone(preg_replace("/([^0-9]+)/", "", $phone));
            $data->setCelphone(preg_replace("/([^0-9]+)/", "", $celphone));
            $data->setBank($bank);
            $data->setAgency($agency);
            $data->setAccount($account);
            $data->setNameBank($nameBank);
            $data->setCpfBank(preg_replace("/([^0-9]+)/", "", $cpfBank));
            $data->setCnpjBank(preg_replace("/([^0-9]+)/", "", $cnpjBank));

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
     * @Route("/prestador-servico/criar/", name="CreateProvider")
     */
    public function createProviderPageAction()
    {
        $em = $this->getDoctrine()->getManager();

        $sp = $em->getRepository("BlzBundle:State")->findOneById(26);
        $states = $em->getRepository("BlzBundle:State")->findAll();
        $city = $em->getRepository("BlzBundle:City")->findByState($sp);
        $franchisee = $em->getRepository("BlzBundle:User")->findBy(array(
            "active" => true,
            "type" => $em->getRepository("BlzBundle:UserType")->findOneById(2)
        ));
        $plan = $em->getRepository("BlzBundle:Plan")->findBy(array(
            "active" => true
        ));

        return $this->render("BlzBundle:Admin:provider-create.html.twig", array(
            "states" => $states,
            "city" => $city,
            "franchisee" => $franchisee,
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
        $description = $request->request->get("description");
        $franchisee = $request->request->get("franchisee");
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
            if(empty($name) || empty($sponsorName) || empty($description) || empty($plan) || empty($birth) || empty($email) || empty($password) || empty($cep) || empty($street) || empty($neighborhood) || empty($number) || empty($city)){
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

            $userType = $em->getRepository("BlzBundle:UserType")->findOneById(3);

            if(empty($userType)){
                throw new \Exception("Tipo de usuário não encontrado", 404);
            }

            $checkUser = $em->getRepository("BlzBundle:User")->findOneByEmail($email);

            if(!empty($checkUser)){
                throw new \Exception("Já existe um usuário com este email", 500);
            }

            $address = new Address;
            $address->setCep(preg_replace("/([^0-9]+)/", "", $cep));
            $address->setStreet($street);
            $address->setNeighborhood($neighborhood);
            $address->setNumber($number);
            $address->setCity($city);

            $em->persist($address);

            $data = new ProviderData;
            $data->setName($sponsorName);
            $data->setDescription($description);
            $data->setBirth((new \Datetime)->createFromFormat("d/m/Y", $birth));
            $data->setCpf(preg_replace("/([^0-9]+)/", "", $cpf));
            $data->setCnpj(preg_replace("/([^0-9]+)/", "", $cnpj));
            $data->setPhone(preg_replace("/([^0-9]+)/", "", $phone));
            $data->setCelphone(preg_replace("/([^0-9]+)/", "", $celphone));
            $data->setCoordX($coordX);
            $data->setCoordY($coordY);
            $data->setAddress($address);
            $data->setHomeCare($homeCare);
            $data->setPlan($plan);

            if(!empty($franchisee)){
                $franchisee = $em->getRepository("BlzBundle:User")->findOneBy(array(
                    "id" => $franchisee,
                    "active" => true
                ));

                if(empty($franchisee)){
                    throw new \Exception("Franqueado não encontrado", 404);
                }

                $data->setFranchisee($franchisee);
            }

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
     * @Route("/prestadores-servico/", name="ListProvider")
     */
    public function listProviderAction()
    {
        $em = $this->getDoctrine()->getManager();

        $provider = $em->getRepository("BlzBundle:User")->findBy(array(
            "type" => $em->getRepository("BlzBundle:UserType")->findOneById(3)
        ), array(
            "id" => "DESC"
        ));

        return $this->render("BlzBundle:Admin:provider-list.html.twig", array(
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

            $provider = $em->getRepository("BlzBundle:User")->findOneById($id);

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
        $em = $this->getDoctrine()->getManager();

        $provider = $em->getRepository("BlzBundle:User")->findOneBy(array(
            "id" => $id,
            "active" => true,
            "type" => $em->getRepository("BlzBundle:UserType")->findOneById(3)
        ));

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

        return $this->render("BlzBundle:Admin:provider-edit.html.twig", array(
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
        $franchisee = $request->request->get("franchisee");
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

            if(!empty($franchisee)){
                $franchisee = $em->getRepository("BlzBundle:User")->findOneBy(array(
                    "id" => $franchisee,
                    "active" => true
                ));

                if(empty($franchisee)){
                    throw new \Exception("Franqueado não encontrado", 404);
                }

                $data->setFranchisee($franchisee);
            }

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
        $em = $this->getDoctrine()->getManager();

        $provider = $em->getRepository("BlzBundle:User")->findOneBy(array(
            "id" => $id,
            "active" => true,
            "type" => $em->getRepository("BlzBundle:UserType")->findOneById(3)
        ));

        $service = $em->getRepository("BlzBundle:ProviderService")->findBy(array(
            "deleted" => false,
            "user" => $provider
        ), array(
            "priority" => "ASC"
        ));

        return $this->render("BlzBundle:Admin:service-list.html.twig", array(
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
        $em = $this->getDoctrine()->getManager();

        $service = $em->getRepository("BlzBundle:ProviderService")->findOneBy(array(
            "id" => $id,
            "deleted" => false
        ));

        if(empty($service)){
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

        return $this->render("BlzBundle:Admin:service-edit.html.twig", array(
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
        $em = $this->getDoctrine()->getManager();

        $provider = $em->getRepository("BlzBundle:User")->findOneBy(array(
            "id" => $id,
            "active" => true,
            "type" => $em->getRepository("BlzBundle:UserType")->findOneById(3)
        ));

        $availableDate = $em->getRepository("BlzBundle:AvailableDate")->findByUser($provider);

        $dates = array();

        for($i = 0; $i < count($availableDate); $i = $i + 1){
            $dates[] = $availableDate[$i]->getDate()->format("Y-m-d");
        }

        return $this->render("BlzBundle:Admin:schedule.html.twig", array(
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

            $provider = $em->getRepository("BlzBundle:User")->findOneBy(array(
                "id" => $id,
                "active" => true,
                "type" => $em->getRepository("BlzBundle:UserType")->findOneById(3)
            ));

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
        $em = $this->getDoctrine()->getManager();

        $provider = $em->getRepository("BlzBundle:User")->findOneBy(array(
            "id" => $id,
            "active" => true,
            "type" => $em->getRepository("BlzBundle:UserType")->findOneById(3)
        ));

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

        return $this->render("BlzBundle:Admin:scheduling.html.twig", array(
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
        $em = $this->getDoctrine()->getManager();
        
        $scheduling = $em->getRepository("BlzBundle:Scheduling")->findOneById($id);
        $schedulingServices = $em->getRepository("BlzBundle:SchedulingService")->findByScheduling($scheduling);

        $scheduling->totalAmount = 0;
        for($i = 0; $i < count($schedulingServices); $i = $i + 1){
            $scheduling->totalAmount = $scheduling->totalAmount + $schedulingServices[$i]->getPrice();
        }

        return $this->render("BlzBundle:Admin:scheduling-details.html.twig", array(
            "scheduling" => $scheduling,
            "scheduling_services" => $schedulingServices
        ));
    }

    /**
     * @Route("/prestador-servico/{id}/financeiro/")
     */
    public function financesProviderAction($id)
    {        
        $em = $this->getDoctrine()->getManager();

        $provider = $em->getRepository("BlzBundle:User")->findOneBy(array(
            "id" => $id,
            "active" => true,
            "type" => $em->getRepository("BlzBundle:UserType")->findOneById(3)
        ));

        $finances = $em->getRepository("BlzBundle:Scheduling")->findBy(array(
            "provider" => $provider,
            "status" => $em->getRepository("BlzBundle:SchedulingStatus")->findOneById(3)
        ), array(
            "id" => "DESC"
        ));

        return $this->render("BlzBundle:Admin:finances.html.twig", array(
            "finances" => $finances
        ));
    }

    /**
     * @Route("/clientes/", name="ListClient")
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

        return $this->render("BlzBundle:Admin:client-list.html.twig", array(
            "client" => $client
        ));
    }

    /**
     * @Route("/categoria/criar/", name="CreateCategory")
     */
    public function createCategoryPageAction()
    {
        return $this->render("BlzBundle:Admin:category-create.html.twig");
    }
    
    /**
     * @Route("/category/create/")
     * @Method("POST")
     */
    public function createCategoryAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $name = $request->request->get("name");
        $description = $request->request->get("description");
        $priority = $request->request->get("priority");
        $photo = $request->request->get("photo");

        try{
            if(empty($name) || empty($description)){
                throw new \Exception("Dados incompletos", 500);
            }

            $photo = json_decode($photo);

            if(count($photo) == 0){
                throw new \Exception("É necessário uma imagem para a categoria", 500);
            }

            $category = new Category;
            $category->setName($name);
            $category->setDescription($description);
            $category->setPriority($priority);

            $filename = md5(uniqid()) . ".png";
            $photo[0] = base64_decode(str_replace(" ", "+", str_replace("data:image/png;base64,", "", $photo[0])));

            if(file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/upload/category/" . $filename, $photo[0])){
                $category->setPhoto($filename);
            }
            else{
                throw new \Exception("Ocorreu um erro no upload da foto", 500);
            }

            $em->persist($category);
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
     * @Route("/categorias/", name="ListCategory")
     */
    public function listCategoryAction()
    {
        $em = $this->getDoctrine()->getManager();

        $category = $em->getRepository("BlzBundle:Category")->findBy(array(
            "active" => true
        ), array(
            "priority" => "ASC"
        ));

        return $this->render("BlzBundle:Admin:category-list.html.twig", array(
            "category" => $category
        ));
    }

    /**
     * @Route("/category/delete/")
     * @Method("POST")
     */
    public function deleteCategoryAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");

        try{
            if(empty($id)){
                throw new \Exception("Dados incompletos", 500);
            }

            $category = $em->getRepository("BlzBundle:Category")->findOneBy(array(
                "id" => $id,
                "active" => true
            ));

            if(empty($category)){
                throw new \Exception("Categoria não encontrada", 404);
            }

            $category->setActive(false);

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
     * @Route("/categoria/{id}/")
     */
    public function editCategoryPageAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $category = $em->getRepository("BlzBundle:Category")->findOneBy(array(
            "id" => $id,
            "active" => true
        ));

        return $this->render("BlzBundle:Admin:category-edit.html.twig", array(
            "category" => $category
        ));
    }

    /**
     * @Route("/category/edit/")
     * @Method("POST")
     */
    public function editCategoryAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");
        $name = $request->request->get("name");
        $description = $request->request->get("description");
        $priority = $request->request->get("priority");
        $photo = $request->request->get("photo");

        try{
            if(empty($name) || empty($description) || empty($id)){
                throw new \Exception("Dados incompletos", 500);
            }

            $category = $em->getRepository("BlzBundle:Category")->findOneById($id);

            if(empty($category)){
                throw new \Exception("Categoria não encontrada", 404);
            }

            $category->setName($name);
            $category->setDescription($description);
            $category->setPriority($priority);

            $photo = json_decode($photo);

            if(count($photo) > 0){
                $filename = md5(uniqid()) . ".png";
                $photo[0] = base64_decode(str_replace(" ", "+", str_replace("data:image/png;base64,", "", $photo[0])));
    
                if(file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/upload/category/" . $filename, $photo[0])){
                    if(is_file($_SERVER["DOCUMENT_ROOT"] . "/upload/category/" . $category->getPhoto())){
                        unlink($_SERVER["DOCUMENT_ROOT"] . "/upload/category/" . $category->getPhoto());
                    }
                    $category->setPhoto($filename);
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
     * @Route("/especialidade/criar/", name="CreateSpecialty")
     */
    public function createSpecialtyPageAction()
    {
        $em = $this->getDoctrine()->getManager();

        $category = $em->getRepository("BlzBundle:Category")->findByActive(true);

        return $this->render("BlzBundle:Admin:specialty-create.html.twig", array(
            "category" => $category
        ));
    }

    /**
     * @Route("/specialty/create/")
     * @Method("POST")
     */
    public function createSpecialtyAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $name = $request->request->get("name");
        $description = $request->request->get("description");
        $priority = $request->request->get("priority");
        $category = $request->request->get("category");

        try{
            if(empty($name) || empty($description) || empty($category)){
                throw new \Exception("Dados incompletos", 500);
            }

            $category = $em->getRepository("BlzBundle:Category")->findOneById($category);

            if(empty($category)){
                throw new \Exception("Categoria não encontrada", 404);
            }

            $specialty = new Specialty;
            $specialty->setName($name);
            $specialty->setDescription($description);
            $specialty->setPriority($priority);
            $specialty->setCategory($category);

            $em->persist($specialty);
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
     * @Route("/categoria/{id}/especialidades/", name="ListSpecialty")
     */
    public function listSpecialtyAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $specialty = $em->getRepository("BlzBundle:Specialty")->findBy(array(
            "active" => true,
            "category" => $em->getRepository("BlzBundle:Category")->findOneById($id)
        ), array(
            "priority" => "ASC"
        ));

        return $this->render("BlzBundle:Admin:specialty-list.html.twig", array(
            "specialty" => $specialty
        ));
    }

    /**
     * @Route("/specialty/delete/")
     * @Method("POST")
     */
    public function deleteSpecialtyAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");

        try{
            if(empty($id)){
                throw new \Exception("Dados incompletos", 500);
            }

            $specialty = $em->getRepository("BlzBundle:Specialty")->findOneBy(array(
                "id" => $id,
                "active" => true
            ));

            if(empty($specialty)){
                throw new \Exception("Especialidade não encontrada", 404);
            }

            $specialty->setActive(false);

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
     * @Route("/especialidade/{id}/")
     */
    public function editSpecialtyPageAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $specialty = $em->getRepository("BlzBundle:Specialty")->findOneBy(array(
            "id" => $id,
            "active" => true
        ));

        return $this->render("BlzBundle:Admin:specialty-edit.html.twig", array(
            "specialty" => $specialty
        ));
    }

    /**
     * @Route("/specialty/edit/")
     * @Method("POST")
     */
    public function editSpecialtyAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $name = $request->request->get("name");
        $description = $request->request->get("description");
        $priority = $request->request->get("priority");
        $id = $request->request->get("id");

        try{
            if(empty($name) || empty($description) || empty($id)){
                throw new \Exception("Dados incompletos", 500);
            }

            $specialty = $em->getRepository("BlzBundle:Specialty")->findOneById($id);

            if(empty($specialty)){
                throw new \Exception("Especialidade não encontrada", 404);
            }

            $specialty->setName($name);
            $specialty->setDescription($description);
            $specialty->setPriority($priority);

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
     * @Route("/plano/criar/", name="CreatePlan")
     */
    public function createPlanPageAction()
    {
        return $this->render("BlzBundle:Admin:plan-create.html.twig");
    }

    /**
     * @Route("/plan/create/")
     * @Method("POST")
     */
    public function creatPlanAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $name = $request->request->get("name");
        $description = $request->request->get("description");
        $amount = $request->request->get("amount");
        $numberOfServices = $request->request->get("number_of_services");
        $numberOfAds = $request->request->get("number_of_ads");

        try{
            if(empty($name) || empty($description) || empty($amount) || empty($numberOfServices) || empty($numberOfAds)){
                throw new \Exception("Dados incompletos", 500);
            }

            $plan = new Plan;
            $plan->setName($name);
            $plan->setDescription($description);
            $plan->setAmount(preg_replace(array("/(,)/", "/([^0-9\.]+)/"), array(".", ""), $amount));
            $plan->setNumberOfServices($numberOfServices);
            $plan->setNumberOfAds($numberOfAds);

            $em->persist($plan);
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
     * @Route("/planos/", name="ListPlan")
     */
    public function listPlanAction()
    {
        $em = $this->getDoctrine()->getManager();

        $plan = $em->getRepository("BlzBundle:Plan")->findBy(array(
            "active" => true,
        ));

        return $this->render("BlzBundle:Admin:plan-list.html.twig", array(
            "plan" => $plan
        ));
    }

    /**
     * @Route("/plan/delete/")
     * @Method("POST")
     */
    public function deletePlanAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");

        try{
            if(empty($id)){
                throw new \Exception("Dados incompletos", 500);
            }

            $plan = $em->getRepository("BlzBundle:Plan")->findOneBy(array(
                "id" => $id,
                "active" => true
            ));

            if(empty($plan)){
                throw new \Exception("Plano não encontrado", 404);
            }

            $plan->setActive(false);

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
     * @Route("/plano/{id}/")
     */
    public function editPlanPageAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $plan = $em->getRepository("BlzBundle:Plan")->findOneBy(array(
            "id" => $id,
            "active" => true
        ));

        return $this->render("BlzBundle:Admin:plan-edit.html.twig", array(
            "plan" => $plan
        ));
    }

    /**
     * @Route("/plan/edit/")
     * @Method("POST")
     */
    public function editPlanAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");
        $name = $request->request->get("name");
        $description = $request->request->get("description");
        $amount = $request->request->get("amount");
        $numberOfServices = $request->request->get("number_of_services");
        $numberOfAds = $request->request->get("number_of_ads");

        try{
            if(empty($id) || empty($name) || empty($description) || empty($amount) || empty($numberOfServices) || empty($numberOfAds)){
                throw new \Exception("Dados incompletos", 500);
            }

            $plan = $em->getRepository("BlzBundle:Plan")->findOneBy(array(
                "id" => $id,
                "active" => true
            ));
            
            if(empty($plan)){
                throw new \Exception("Plano não encontrado", 404);
            }
            
            $plan->setName($name);
            $plan->setDescription($description);
            $plan->setAmount(preg_replace(array("/(,)/", "/([^0-9\.]+)/"), array(".", ""), $amount));
            $plan->setNumberOfServices($numberOfServices);
            $plan->setNumberOfAds($numberOfAds);

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
     * @Route("/anuncio/criar/", name="CreateAd")
     */
    public function createAdPageAction()
    {
        $em = $this->getDoctrine()->getManager();

        $provider = $em->getRepository("BlzBundle:User")->findBy(array(
            "active" => true,
            "type" => $em->getRepository("BlzBundle:UserType")->findOneById(3)
        ));

        return $this->render("BlzBundle:Admin:ad-create.html.twig", array(
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

            $photo = json_decode($photo);

            if(count($photo) == 0){
                throw new \Exception("É necessário uma imagem para o anúncio", 500);
            }

            $provider = $em->getRepository("BlzBundle:User")->findOneBy(array(
                "id" => $provider,
                "active" => true,
                "type" => $em->getRepository("BlzBundle:UserType")->findOneById(3)
            ));

            if(empty($provider)){
                throw new \Exception("Prestador de serviço não encontrado", 404);
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
     * @Route("/anuncios/", name="ListAd")
     */
    public function listAdAction()
    {
        $em = $this->getDoctrine()->getManager();

        $ad = $em->getRepository("BlzBundle:Ad")->findBy(array(
            "active" => true
        ), array(
            "id" => "DESC"
        ));

        return $this->render("BlzBundle:Admin:ad-list.html.twig", array(
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
        $em = $this->getDoctrine()->getManager();

        $ad = $em->getRepository("BlzBundle:Ad")->findOneBy(array(
            "id" => $id,
            "active" => true
        ));

        $provider = $em->getRepository("BlzBundle:User")->findBy(array(
            "active" => true,
            "type" => $em->getRepository("BlzBundle:UserType")->findOneById(3)
        ));

        return $this->render("BlzBundle:Admin:ad-edit.html.twig", array(
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
        $provider = $request->request->get("provider");
        $photo = $request->request->get("photo");

        try{
            if(empty($name) || empty($description) || empty($id) || empty($provider)){
                throw new \Exception("Dados incompletos", 500);
            }

            $ad = $em->getRepository("BlzBundle:Ad")->findOneById($id);

            if(empty($ad)){
                throw new \Exception("Anúncio não encontrado", 404);
            }

            $provider = $em->getRepository("BlzBundle:User")->findOneBy(array(
                "id" => $provider,
                "active" => true,
                "type" => $em->getRepository("BlzBundle:UserType")->findOneById(3)
            ));

            if(empty($provider)){
                throw new \Exception("Prestador de serviço não encontrado", 404);
            }

            $ad->setName($name);
            $ad->setDescription($description);
            $ad->setProvider($provider);

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
     * @Route("/premiacao/criar/", name="CreateAward")
     */
    public function createAwardPageAction()
    {
        return $this->render("BlzBundle:Admin:award-create.html.twig");
    }
    
    /**
     * @Route("/award/create/")
     * @Method("POST")
     */
    public function createAwardAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $name = $request->request->get("name");
        $description = $request->request->get("description");
        $points = $request->request->get("points");
        $photo = $request->request->get("photo");

        try{
            if(empty($name) || empty($description) || empty($points)){
                throw new \Exception("Dados incompletos", 500);
            }

            $photo = json_decode($photo);

            if(count($photo) == 0){
                throw new \Exception("É necessário uma imagem para a premiação", 500);
            }

            $award = new Award;
            $award->setName($name);
            $award->setDescription($description);
            $award->setPoints($points);

            $filename = md5(uniqid()) . ".png";
            $photo[0] = base64_decode(str_replace(" ", "+", str_replace("data:image/png;base64,", "", $photo[0])));

            if(file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/upload/award/" . $filename, $photo[0])){
                $award->setPhoto($filename);
            }
            else{
                throw new \Exception("Ocorreu um erro no upload da foto", 500);
            }

            $em->persist($award);
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
     * @Route("/premiacoes/", name="ListAward")
     */
    public function listAwardAction()
    {
        $em = $this->getDoctrine()->getManager();

        $award = $em->getRepository("BlzBundle:Award")->findByActive(true);

        return $this->render("BlzBundle:Admin:award-list.html.twig", array(
            "award" => $award
        ));
    }

    /**
     * @Route("/award/delete/")
     * @Method("POST")
     */
    public function deleteAwardAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");

        try{
            if(empty($id)){
                throw new \Exception("Dados incompletos", 500);
            }

            $award = $em->getRepository("BlzBundle:Award")->findOneBy(array(
                "id" => $id,
                "active" => true
            ));

            if(empty($award)){
                throw new \Exception("Premiação não encontrada", 404);
            }

            $award->setActive(false);

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
     * @Route("/premiacao/{id}/")
     */
    public function editAwardPageAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $award = $em->getRepository("BlzBundle:Award")->findOneBy(array(
            "id" => $id,
            "active" => true
        ));

        return $this->render("BlzBundle:Admin:award-edit.html.twig", array(
            "award" => $award
        ));
    }

    /**
     * @Route("/award/edit/")
     * @Method("POST")
     */
    public function editAwardAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");
        $name = $request->request->get("name");
        $description = $request->request->get("description");
        $points = $request->request->get("points");
        $photo = $request->request->get("photo");

        try{
            if(empty($name) || empty($description) || empty($points) || empty($id)){
                throw new \Exception("Dados incompletos", 500);
            }

            $award = $em->getRepository("BlzBundle:Award")->findOneById($id);

            if(empty($award)){
                throw new \Exception("Premiação não encontrada", 404);
            }

            $award->setName($name);
            $award->setDescription($description);
            $award->setPoints($points);

            $photo = json_decode($photo);

            if(count($photo) > 0){
                $filename = md5(uniqid()) . ".png";
                $photo[0] = base64_decode(str_replace(" ", "+", str_replace("data:image/png;base64,", "", $photo[0])));
    
                if(file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/upload/award/" . $filename, $photo[0])){
                    if(is_file($_SERVER["DOCUMENT_ROOT"] . "/upload/award/" . $award->getPhoto())){
                        unlink($_SERVER["DOCUMENT_ROOT"] . "/upload/award/" . $award->getPhoto());
                    }
                    $award->setPhoto($filename);
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
     * @Route("/solicitacao-premiacao/", name="AwardRequest")
     */
    public function awardRequestAction()
    {
        $em = $this->getDoctrine()->getManager();

        $award = $em->getRepository("BlzBundle:AwardRequest")->findByDelivered(false);

        return $this->render("BlzBundle:Admin:award-request.html.twig", array(
            "award" => $award
        ));
    }

    /**
     * @Route("/award/delivery/")
     * @Method("POST")
     */
    public function deliveryAwardAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");

        try{
            if(empty($id)){
                throw new \Exception("Dados incompletos", 500);
            }

            $award = $em->getRepository("BlzBundle:AwardRequest")->findOneBy(array(
                "id" => $id,
                "delivered" => false
            ));

            if(empty($award)){
                throw new \Exception("Solicitação de premiação não encontrada", 404);
            }

            $userPoints = new UserPoint;
            $userPoints->setUser($award->getUser());
            $userPoints->setPoints(-$award->getPoints());

            $em->persist($userPoints);

            $award->setDelivered(true);

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
     * @Route("/configuracoes/", name="Configuration")
     */
    public function configurationPageAction()
    {
        $em = $this->getDoctrine()->getManager();

        $systemTax = $em->getRepository("BlzBundle:Configuration")->findOneByKey("system_tax");
        $franchiseeTax = $em->getRepository("BlzBundle:Configuration")->findOneByKey("franchisee_tax");
        $franchiseePrivacy = $em->getRepository("BlzBundle:Configuration")->findOneByKey("franchisee_privacy");
        $providerPrivacy = $em->getRepository("BlzBundle:Configuration")->findOneByKey("provider_privacy");
        $userPrivacy = $em->getRepository("BlzBundle:Configuration")->findOneByKey("user_privacy");

        return $this->render("BlzBundle:Admin:configuration.html.twig", array(
            "systemTax" => $systemTax,
            "franchiseeTax" => $franchiseeTax,
            "franchiseePrivacy" => $franchiseePrivacy,
            "providerPrivacy" => $providerPrivacy,
            "userPrivacy" => $userPrivacy
        ));
    }

    /**
     * @Route("/configuration/")
     */
    public function configurationAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $systemTax = $request->request->get("system_tax");
        $franchiseeTax = $request->request->get("franchisee_tax");
        $franchiseePrivacy = $request->request->get("franchisee_privacy");
        $providerPrivacy = $request->request->get("provider_privacy");
        $userPrivacy = $request->request->get("user_privacy");

        try{
            $configuration = $em->getConnection()->prepare("UPDATE `configuration` SET `value` = ? WHERE `key` = 'system_tax';
            UPDATE `configuration` SET `value` = ? WHERE `key` = 'franchisee_tax';
            UPDATE `configuration` SET `value` = ? WHERE `key` = 'franchisee_privacy';
            UPDATE `configuration` SET `value` = ? WHERE `key` = 'provider_privacy';
            UPDATE `configuration` SET `value` = ? WHERE `key` = 'user_privacy';");
            $configuration->execute(array($systemTax, $franchiseeTax, $franchiseePrivacy, $providerPrivacy, $userPrivacy));

            return new Response("success");
        }
        catch(\Exception $e){
            $message = !empty($e->getMessage()) ? $e->getMessage() : "Ocorreu um erro";
            $status = !empty($e->getCode()) ? $e->getCode() : 500;

            return new Response($message, $status);
        }
    }

    /**
     * @Route("/metodo-pagamento/criar/", name="CreatePaymentMethod")
     */
    public function createPaymentMethodPageAction()
    {
        return $this->render("BlzBundle:Admin:payment-method-create.html.twig");
    }
    
    /**
     * @Route("/payment-method/create/")
     * @Method("POST")
     */
    public function createPaymentMethodAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $name = $request->request->get("name");
        $description = $request->request->get("description");
        $photo = $request->request->get("photo");

        try{
            if(empty($name) || empty($description)){
                throw new \Exception("Dados incompletos", 500);
            }

            $paymentMethod = new PaymentMethod;
            $paymentMethod->setName($name);
            $paymentMethod->setDescription($description);

            $photo = json_decode($photo);

            if(count($photo) > 0){
                $filename = md5(uniqid()) . ".png";
                $photo[0] = base64_decode(str_replace(" ", "+", str_replace("data:image/png;base64,", "", $photo[0])));

                if(file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/upload/payment-method/" . $filename, $photo[0])){
                    $paymentMethod->setPhoto($filename);
                }
                else{
                    throw new \Exception("Ocorreu um erro no upload da foto", 500);
                }
            }

            $em->persist($paymentMethod);
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
     * @Route("/metodos-pagamento/", name="ListPaymentMethod")
     */
    public function listPaymentMethodAction()
    {
        $em = $this->getDoctrine()->getManager();

        $paymentMethod = $em->getRepository("BlzBundle:PaymentMethod")->findByActive(true);

        return $this->render("BlzBundle:Admin:payment-method-list.html.twig", array(
            "payment_method" => $paymentMethod
        ));
    }

    /**
     * @Route("/payment-method/delete/")
     * @Method("POST")
     */
    public function deletePaymentMethodAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");

        try{
            if(empty($id)){
                throw new \Exception("Dados incompletos", 500);
            }

            $paymentMethod = $em->getRepository("BlzBundle:PaymentMethod")->findOneBy(array(
                "id" => $id,
                "active" => true
            ));

            if(empty($paymentMethod)){
                throw new \Exception("Método de pagamento não encontrado", 404);
            }

            $paymentMethod->setActive(false);

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
     * @Route("/metodo-pagamento/{id}/")
     */
    public function editPaymentMethodPageAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $paymentMethod = $em->getRepository("BlzBundle:PaymentMethod")->findOneBy(array(
            "id" => $id,
            "active" => true
        ));

        return $this->render("BlzBundle:Admin:payment-method-edit.html.twig", array(
            "payment_method" => $paymentMethod
        ));
    }

    /**
     * @Route("/payment-method/edit/")
     * @Method("POST")
     */
    public function editPaymetnMethodAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");
        $name = $request->request->get("name");
        $description = $request->request->get("description");
        $photo = $request->request->get("photo");

        try{
            if(empty($name) || empty($description) || empty($id)){
                throw new \Exception("Dados incompletos", 500);
            }

            $paymentMethod = $em->getRepository("BlzBundle:PaymentMethod")->findOneById($id);

            if(empty($paymentMethod)){
                throw new \Exception("Método de pagamento não encontrada", 404);
            }

            $paymentMethod->setName($name);
            $paymentMethod->setDescription($description);

            $photo = json_decode($photo);

            if(count($photo) > 0){
                $filename = md5(uniqid()) . ".png";
                $photo[0] = base64_decode(str_replace(" ", "+", str_replace("data:image/png;base64,", "", $photo[0])));
    
                if(file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/upload/payment-method/" . $filename, $photo[0])){
                    if(is_file($_SERVER["DOCUMENT_ROOT"] . "/upload/payment-method/" . $paymentMethod->getPhoto())){
                        unlink($_SERVER["DOCUMENT_ROOT"] . "/upload/payment-method/" . $paymentMethod->getPhoto());
                    }
                    $paymentMethod->setPhoto($filename);
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
     * @Route("/financeiro/", name="FinancesAdmin")
     */
    public function financesAction()
    {
        $em = $this->getDoctrine()->getManager();

        $finances = $em->getRepository("BlzBundle:Scheduling")->findBy(array(
            "status" => $em->getRepository("BlzBundle:SchedulingStatus")->findOneById(3)
        ), array(
            "id" => "DESC"
        ));

        return $this->render("BlzBundle:Admin:finances.html.twig", array(
            "finances" => $finances
        ));
    }

    /**
     * @Route("/estatisticas/agendamentos/", name="StatsScheduling")
     */
    public function statsSchedulingAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $franchisee = $request->request->get("franchisee");
        $dateMin = $request->request->get("date_min");
        $dateMax = $request->request->get("date_max");

        $franchisees = $em->getRepository("BlzBundle:User")->findBy(array(
            "active" => true,
            "type" => $em->getRepository("BlzBundle:UserType")->findOneById(2)
        ));

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
            "franchisee" => $franchisee,
            "date_min" => $date_min ? $date_min->format("Y-m-d") : NULL,
            "date_max" => $date_max ? $date_max->format("Y-m-d") : NULL
        ));

        return $this->render("BlzBundle:Admin:stats-scheduling.html.twig", array(
            "franchisee" => $franchisee,
            "date_min" => $dateMin,
            "date_max" => $dateMax,
            "franchisees" => $franchisees,
            "scheduling" => $scheduling
        ));
    }

    /**
     * @Route("/estatisticas/movimentacoes/", name="StatsFinance")
     */
    public function statsFinanceAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $franchisee = $request->request->get("franchisee");
        $dateMin = $request->request->get("date_min");
        $dateMax = $request->request->get("date_max");

        $franchisees = $em->getRepository("BlzBundle:User")->findBy(array(
            "active" => true,
            "type" => $em->getRepository("BlzBundle:UserType")->findOneById(2)
        ));

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
            "franchisee" => $franchisee,
            "date_min" => $date_min ? $date_min->format("Y-m-d") : NULL,
            "date_max" => $date_max ? $date_max->format("Y-m-d") : NULL
        ));

        return $this->render("BlzBundle:Admin:stats-finance.html.twig", array(
            "franchisee" => $franchisee,
            "date_min" => $dateMin,
            "date_max" => $dateMax,
            "franchisees" => $franchisees,
            "finances" => $finances
        ));
    }

    /**
     * @Route("/estatisticas/preco-medio/", name="StatsAvgPrice")
     */
    public function statsAvgPriceAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $franchisee = $request->request->get("franchisee");
        $dateMin = $request->request->get("date_min");
        $dateMax = $request->request->get("date_max");

        $franchisees = $em->getRepository("BlzBundle:User")->findBy(array(
            "active" => true,
            "type" => $em->getRepository("BlzBundle:UserType")->findOneById(2)
        ));

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
            "franchisee" => $franchisee,
            "date_min" => $date_min ? $date_min->format("Y-m-d") : NULL,
            "date_max" => $date_max ? $date_max->format("Y-m-d") : NULL
        ));

        return $this->render("BlzBundle:Admin:stats-avg-price.html.twig", array(
            "franchisee" => $franchisee,
            "date_min" => $dateMin,
            "date_max" => $dateMax,
            "franchisees" => $franchisees,
            "services" => $services
        ));
    }

    /**
     * @Route("/estatisticas/mais-agendados/", name="StatsTopScheduling")
     */
    public function statsTopSchedulingAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $franchisee = $request->request->get("franchisee");
        $dateMin = $request->request->get("date_min");
        $dateMax = $request->request->get("date_max");

        $franchisees = $em->getRepository("BlzBundle:User")->findBy(array(
            "active" => true,
            "type" => $em->getRepository("BlzBundle:UserType")->findOneById(2)
        ));

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
            "franchisee" => $franchisee,
            "date_min" => $date_min ? $date_min->format("Y-m-d") : NULL,
            "date_max" => $date_max ? $date_max->format("Y-m-d") : NULL
        ));

        return $this->render("BlzBundle:Admin:stats-top-scheduling.html.twig", array(
            "franchisee" => $franchisee,
            "date_min" => $dateMin,
            "date_max" => $dateMax,
            "franchisees" => $franchisees,
            "services" => $services
        ));
    }
}
