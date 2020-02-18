<?php
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Slim\Factory\AppFactory;
    use Slim\Views\Twig;
    use Slim\Views\TwigMiddleware;

    // autoloader
    require __DIR__ . '/vendor/autoload.php';
    // models
    require_once __DIR__ . '/src/models/Login.php';
    // views

    if(!isset($_SESSION)) {
        session_start();
    }    
    
    // Instantiate App
    $app = AppFactory::create();
    $app->setBasePath("/calvary-web-2");

    // // Add error middleware
    $app->addErrorMiddleware(true, true, true);

    // Create Twig
    $twig = Twig::create(__DIR__ . '/src/views', ['cache' => false]);

    // Add Twig-View Middleware
    $app->add(TwigMiddleware::create($app, $twig));

    // Define named route

    // ### default root 
    $app->get('/', function ($request, $response, $args) {
        $view = Twig::fromRequest($request);

        // check login
        if(!isset($_SESSION['userID'])) {
            // display login page
            return $view->render($response, 'login.twig');
        } else {
            // display main page

            return $view->render($response, 'home.twig', ['login_id' => $_SESSION['userID'], 'login_name' => Login::getLoginName($_SESSION['userID'])]);
            // return $view->render($response, 'main.twig', ['username' => $_SESSION['userID']]);
        }
    })->setName('root');

    // ### check login id
    $app->post('/logincheck', function ($request, $response, $args) {
        $view = Twig::fromRequest($request);

        $user = $_POST["userID"];
        $pw = $_POST["userPassword"];
        $loginCheck = false;
        $loginModel = new Login();
        $loginCheck = $loginModel->checkLogin($user, $pw);
        if($loginCheck) {
            $_SESSION['userID'] = $user;
            return $response->withHeader('Location', '/calvary-web-2/')->withStatus(302);
        } else {
            return $view->render($response, 'login_fail.twig');
        }
    })->setName('logincheck');

    // ### logout
    $app->get('/logout', function ($request, $response, $args) {
        $view = Twig::fromRequest($request);

        session_unset();
        session_destroy();
        return $response->withHeader('Location', '/calvary-web-2/')->withStatus(302);
    })->setName('logout');

    // ### admin
    // ### member register
    $app->get('/members/register', function ($request, $response, $args) {
        $view = Twig::fromRequest($request);

        return $view->render($response, 'members_register.twig', ['login_id' => $_SESSION['userID'], 'login_name' => Login::getLoginName($_SESSION['userID'])]);
    })->setName('members_register');

    // ### member register search
    $app->post('/members/register/search', function ($request, $response, $args) {
        $view = Twig::fromRequest($request);

        $inputInfo['id'] = $_POST["member_id"];
        $inputInfo['name'] = $_POST["member_name"];
        $inputInfo['part'] = $_POST["member_part"];

        require_once __DIR__ . '/src/models/Member.php';
        require_once __DIR__ . '/src/models/Part.php';
        require_once __DIR__ . '/src/models/ChurchStaff.php';
        require_once __DIR__ . '/src/models/MemberState.php';
        $memberModel = new Member();
        $searchResult = $memberModel->getMemberSearchResult($inputInfo['id'], $inputInfo['name'], Part::getPartNumber($inputInfo['part']));

        if(count($searchResult) > 0) {
            //
            for($rowIdx=0; $rowIdx<count($searchResult); $rowIdx++) {
                $searchResult[$rowIdx]['part'] = Part::getPartName($searchResult[$rowIdx]['part']);
                $searchResult[$rowIdx]['church_staff'] = ChurchStaff::getChurchStaffName($searchResult[$rowIdx]['church_staff']);
                $searchResult[$rowIdx]['last_state'] = MemberState::getMemberStateName($searchResult[$rowIdx]['last_state']);
            }

            return $view->render($response, 'members_register_search.twig', ['input_info' => $inputInfo, 'search_result' => $searchResult, 'login_id' => $_SESSION['userID'], 'login_name' => Login::getLoginName($_SESSION['userID'])]);
        } else {
            return $view->render($response, 'members_register_search_noresult.twig', ['input_info' => $inputInfo, 'search_result' => $searchResult, 'login_id' => $_SESSION['userID'], 'login_name' => Login::getLoginName($_SESSION['userID'])]);
        }
        
    })->setName('members_register_search');

    // ### member register id
    $app->get('/members/register/{id}', function ($request, $response, $args) {
        $view = Twig::fromRequest($request);

        $id = $args['id'];
        if(is_numeric($id)) {
            require_once __DIR__ . '/src/models/Member.php';
            require_once __DIR__ . '/src/models/Part.php';
            require_once __DIR__ . '/src/models/ChurchStaff.php';
            require_once __DIR__ . '/src/models/MemberState.php';
            require_once __DIR__ . '/src/models/CalvaryStaff.php';

            $memberModel = new Member();
            
            // display data
            $searchResult = $memberModel->getMemberSearchResult($id, '', '');
            if(count($searchResult) == 1) {
                //
                $editInfo = array();
                $editInfo['sn'] = $searchResult[0]['sn'];
                $editInfo['name'] = $searchResult[0]['name'];
                $editInfo['part'] = Part::getPartName($searchResult[0]['part']);
                $editInfo['church_staff'] = ChurchStaff::getChurchStaffName($searchResult[0]['church_staff']);
                $editInfo['calvary_staff'] = CalvaryStaff::getCalvaryStaffName($searchResult[0]['calvary_staff']);

                return $view->render($response, 'members_register_id.twig', ['edit_info' => $editInfo, 'login_id' => $_SESSION['userID'], 'login_name' => Login::getLoginName($_SESSION['userID'])]);
            } else {
                return $view->render($response, 'members_register_id_noresult.twig', ['login_id' => $_SESSION['userID'], 'login_name' => Login::getLoginName($_SESSION['userID'])]);
            }
        } else {
            return $view->render($response, 'members_register_id_noresult.twig', ['login_id' => $_SESSION['userID'], 'login_name' => Login::getLoginName($_SESSION['userID'])]);
        }
        
    })->setName('members_register_id');

    // ### member register id edit
    $app->post('/members/register/{id}/edit', function ($request, $response, $args) {
        $view = Twig::fromRequest($request);

        require_once __DIR__ . '/src/models/Member.php';
        require_once __DIR__ . '/src/models/Part.php';
        require_once __DIR__ . '/src/models/ChurchStaff.php';
        require_once __DIR__ . '/src/models/MemberState.php';
        require_once __DIR__ . '/src/models/CalvaryStaff.php';

        $memberModel = new Member();
        $id = $_POST["member_id"];
        $name = $_POST["member_name"];
        $part = Part::getPartNumber($_POST["member_part"]);
        $churchStaff = ChurchStaff::getChurchStaffNumber($_POST["member_church_staff"]);
        $calvaryStaff = CalvaryStaff::getCalvaryStaffNumber($_POST["member_calvary_staff"]);

        if(!empty($name)) {
            $editResult = $memberModel->updateMemberInformation($id, $name, $part, $churchStaff, $calvaryStaff);
            if($editResult) {
                //redirect to members register id
                return $response->withHeader('Location', '/calvary-web-2/members/register/'.$id)->withStatus(302);
            } else {
                $editInfo['sn'] = $_POST["member_id"];
                $editInfo['name'] = $_POST["member_name"];
                $editInfo['part'] = $_POST["member_part"];
                $editInfo['church_staff'] = $_POST["member_church_staff"];
                $editInfo['calvary_staff'] = $_POST["member_calvary_staff"];
                return $view->render($response, 'members_register_id_editerror.twig', ['edit_info' => $editInfo, 'login_id' => $_SESSION['userID'], 'login_name' => Login::getLoginName($_SESSION['userID'])]);
            }
        } else {
            $editInfo = array();
            $editInfo['sn'] = $_POST["member_id"];
            $editInfo['name'] = $_POST["member_name"];
            $editInfo['part'] = $_POST["member_part"];
            $editInfo['church_staff'] = $_POST["member_church_staff"];
            $editInfo['calvary_staff'] = $_POST["member_calvary_staff"];
            return $view->render($response, 'members_register_id_editerror.twig', ['edit_info' => $editInfo, 'login_id' => $_SESSION['userID'], 'login_name' => Login::getLoginName($_SESSION['userID'])]);
        }
        
    })->setName('members_register_id_edit');

    // ### member register search
    $app->post('/members/register/add', function ($request, $response, $args) {
        $view = Twig::fromRequest($request);

        require_once __DIR__ . '/src/models/Member.php';
        require_once __DIR__ . '/src/models/MemberState.php';
        require_once __DIR__ . '/src/models/Part.php';
        require_once __DIR__ . '/src/models/ChurchStaff.php';
        require_once __DIR__ . '/src/models/CalvaryStaff.php';
        require_once __DIR__ . '/src/models/MemberState.php';

        $inputInfo['name'] = $_POST["member_name"];
        $inputInfo['part'] = Part::getPartNumber($_POST["member_part"]);
        $inputInfo['church_staff'] = ChurchStaff::getChurchStaffNumber($_POST["member_church_staff"]);
        $inputInfo['calvary_staff'] = CalvaryStaff::getCalvaryStaffNumber($_POST["member_calvary_staff"]);
        $inputInfo['member_state'] = MemberState::getMemberStateNumber($_POST["member_state"]);
        $inputInfo['member_state_date'] = $_POST["member_state_date"];

        $memberModel = new Member();
        $result = $memberModel->addMember($inputInfo['name'], $inputInfo['part'], $inputInfo['church_staff'], $inputInfo['calvary_staff'], $inputInfo['member_state']);
        $memberStateModel = new MemberState();

        if($result[0] == 0) {
            $updateMsg = 'success';
            $inputInfo['id'] = $result[1];
            $resultMemberState = $memberStateModel->addMemberState($inputInfo['id'], $inputInfo['member_state_date'], $inputInfo['member_state']);
        } elseif ($result[0] == -1) {
            $updateMsg = 'warning';
            $inputInfo['id'] = $result[1];
            $resultMemberState = $memberStateModel->addMemberState($inputInfo['id'], $inputInfo['member_state_date'], $inputInfo['member_state']);
        } else {
            $updateMsg = 'fail';
        }

        return $view->render($response, 'members_register_add.twig', ['message' => $updateMsg, 'input_info' => $inputInfo, 'login_id' => $_SESSION['userID'], 'login_name' => Login::getLoginName($_SESSION['userID'])]);
        
    })->setName('members_register_add');

    $app->run();
