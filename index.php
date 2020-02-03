<?php
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Slim\Factory\AppFactory;
    use Slim\Views\Twig;
    use Slim\Views\TwigMiddleware;

    require __DIR__ . '/vendor/autoload.php';

    require_once __DIR__ . '/src/models/DBConn.php';
    require_once __DIR__ . '/src/models/Login.php';

    if(!isset($_SESSION)) {
        session_start();
    }

    // Instantiate App
    $app = AppFactory::create();
    $app->setBasePath("/calvary_web_2");

    // Add error middleware
    $app->addErrorMiddleware(true, true, true);

    // Create Twig
    $twig = Twig::create(__DIR__ . '/src/views', ['cache' => false]);

    // Add Twig-View Middleware
    $app->add(TwigMiddleware::create($app, $twig));

    // Define named route

    $app->get('/', function ($request, $response, $args) {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'login.twig');
    })->setName('login');

    $app->post('/logincheck', function ($request, $response, $args) {
        $view = Twig::fromRequest($request);
        $db = new DBConn();
        $conn = $db->getNewDBConn();
        $user = $_POST["userID"];
        $pw = $_POST["userPassword"];
        $loginCheck = false;
        $loginCheck = Login::checkLogin($conn, $user, $pw);
        $db->closeDBConn($conn);
        if($loginCheck) {
            $_SESSION['userID'] = $user;
            return $view->render($response, 'main.twig');
        } else {
            return $view->render($response, 'logincheck.twig', ['id' => 'false', 'pw' => 'false']);
        }
        // return $view->render($response, 'logincheck.twig', ['id' => $loginCheck, 'pw' => $loginCheck]);
    })->setName('logincheck');

    $app->get('/main', function ($request, $response, $args) {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'main.twig');
    })->setName('main');

    $app->run();