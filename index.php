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

    // Add error middleware
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

            return $view->render($response, 'main.twig', ['login_id' => $_SESSION['userID'], 'login_name' => Login::getLoginName($_SESSION['userID'])]);
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
    // ### password update
    $app->post('/admin/account/edit', function ($request, $response, $args) {
        $view = Twig::fromRequest($request);

        return $view->render($response, 'password_edit.twig');
    })->setName('admin_account_edit');

    $app->run();
