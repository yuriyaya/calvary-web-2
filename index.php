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

    $app->run();
