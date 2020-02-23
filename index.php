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
        }
    })->setName('root');

    // ### Login - Logout
    //  ## check login id
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

    //  ## logout
    $app->get('/logout', function ($request, $response, $args) {
        $view = Twig::fromRequest($request);

        session_unset();
        session_destroy();
        return $response->withHeader('Location', '/calvary-web-2/')->withStatus(302);
    })->setName('logout');

    // ### admin
    //  ## member search
    $app->get('/members/search', function ($request, $response, $args) {
        $view = Twig::fromRequest($request);
        if(isset($_GET['search'])) {

            $inputInfo['id'] = $_GET["id"];
            $inputInfo['name'] = $_GET["name"];
            $inputInfo['part'] = $_GET["part"];
            $inputInfo['view'] = $_GET["view"];

            require_once __DIR__ . '/src/models/Member.php';
            require_once __DIR__ . '/src/models/Part.php';
            require_once __DIR__ . '/src/models/ChurchStaff.php';
            require_once __DIR__ . '/src/models/MemberState.php';
            $memberModel = new Member();
            $searchResult = $memberModel->getMemberSearchResult($inputInfo['id'], $inputInfo['name'], $inputInfo['part']);

            if($inputInfo['view'] == 1) { // member edit & delete
                if(count($searchResult) > 0) {
                    //
                    for($rowIdx=0; $rowIdx<count($searchResult); $rowIdx++) {
                        $searchResult[$rowIdx]['part'] = Part::getPartName($searchResult[$rowIdx]['part']);
                        $searchResult[$rowIdx]['church_staff'] = ChurchStaff::getChurchStaffName($searchResult[$rowIdx]['church_staff']);
                        $searchResult[$rowIdx]['last_state'] = MemberState::getMemberStateName($searchResult[$rowIdx]['last_state']);
                    }
                    $result = 'success';
                } else {
                    $result = 'fail';
                }
                return $view->render($response, 'members_search_result.twig', ['result'=>$result, 'input_info'=>$inputInfo, 'search_result'=>$searchResult, 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
            } else {
                $title = '대원 정보 조회';
                $message = '대원 정보 조회 실패 - 확인 후 다시 시도해 주세요';
                return $view->render($response, 'members_error.twig', ['title'=>$title, 'message'=>$message, 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
            }
        } else {
            return $view->render($response, 'members_search.twig', ['login_id' => $_SESSION['userID'], 'login_name' => Login::getLoginName($_SESSION['userID'])]);
        }
    })->setName('members_search');

    // ### member id
    $app->get('/members/{id}', function ($request, $response, $args) {
        $view = Twig::fromRequest($request);

        $id = $args['id'];
        if(is_numeric($id)) {
            require_once __DIR__ . '/src/models/Member.php';
            require_once __DIR__ . '/src/models/Part.php';
            require_once __DIR__ . '/src/models/ChurchStaff.php';
            require_once __DIR__ . '/src/models/MemberState.php';
            require_once __DIR__ . '/src/models/CalvaryStaff.php';
            require_once __DIR__ . '/src/models/MemberState.php';

            $memModel = new Member();
            
            // display data
            $searchResult = $memModel->getMemberSearchResult($id, '', '');
            if(count($searchResult) == 1) {
                //
                $editInfo = array();
                $editInfo['sn'] = $searchResult[0]['sn'];
                $editInfo['name'] = $searchResult[0]['name'];
                $editInfo['part'] = Part::getPartName($searchResult[0]['part']);
                $editInfo['church_staff'] = ChurchStaff::getChurchStaffName($searchResult[0]['church_staff']);
                $editInfo['calvary_staff'] = CalvaryStaff::getCalvaryStaffName($searchResult[0]['calvary_staff']);
                $editInfo['last_state'] = MemberState::getMemberStateName($searchResult[0]['last_state']);

                $memStateModel = new MemberState();
                $memberStateResult = $memStateModel->getMemberStateSearchResult($id);
                
                for($rowIdx=0; $rowIdx<count($memberStateResult); $rowIdx++) {
                    $memberStateResult[$rowIdx]['state'] = MemberState::getMemberStateName($memberStateResult[$rowIdx]['state']);
                }

                return $view->render($response, 'members_id.twig', ['state_result'=>$memberStateResult, 'edit_info'=>$editInfo, 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
            } else {
                $title = '대원 정보 조회';
                $message = '대원 정보 조회 실패 - 확인 후 다시 시도해 주세요';
                return $view->render($response, 'members_error.twig', ['title'=>$title, 'message'=>$message, 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
            }
        } else {
            $title = '대원 정보 조회';
            $message = '입력된 아이디 값 에러 - 확인 후 다시 시도해 주세요';
            return $view->render($response, 'members_error.twig', ['title'=>$title, 'message'=>$message, 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
        }
        
    })->setName('members_id');

    // ### member id edit
    $app->post('/members/{id}/edit', function ($request, $response, $args) {
        $view = Twig::fromRequest($request);

        require_once __DIR__ . '/src/models/Member.php';

        $id = $args['id'];
        $memberModel = new Member();

        if(!empty($_POST["member_name"])) {
            $editResult = $memberModel->updateMemberInformation($id, $_POST["member_name"], $_POST["member_part"], $_POST["member_church_staff"], $_POST["member_calvary_staff"]);
            if($editResult) {
                //redirect to members register id
                return $response->withHeader('Location', '/calvary-web-2/members/'.$id)->withStatus(302);
            } else {
                $title = '대원 정보 수정';
                $message = '대원 정보 수정 실패 - 확인 후 다시 시도해 주세요';
                return $view->render($response, 'members_error.twig', ['title'=>$title, 'message'=>$message, 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
            }
        } else {
            $title = '대원 정보 수정';
            $message = '이름 입력값 에러 - 확인 후 다시 시도해 주세요';
            return $view->render($response, 'members_error.twig', ['title'=>$title, 'message'=>$message, 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
        }
        
    })->setName('members_id_edit');

    // ### member register id delete
    $app->get('/members/{id}/delete', function ($request, $response, $args) {
        $view = Twig::fromRequest($request);

        $id = $args['id'];

        require_once __DIR__ . '/src/models/Member.php';
        require_once __DIR__ . '/src/models/MemberState.php';

        $memberModel = new Member();
        $resultMember = $memberModel->deleteMember($id);
        if($resultMember) {
            $memberStateModel = new MemberState();
            $resultState = $memberStateModel->deleteMemberState($id);
            if($resultState) {
                $title = '대원 정보 삭제';
            $message = '대원 정보 삭제 성공 - '.$id.' 대원이 삭제 되었습니다';
            return $view->render($response, 'members_success.twig', ['title'=>$title, 'message'=>$message, 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
            } else {
                $title = '대원 정보 삭제';
            $message = '대원 상태 정보 삭제 에러 - 확인 후 다시 시도해 주세요';
            return $view->render($response, 'members_error.twig', ['title'=>$title, 'message'=>$message, 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
            }
        } else {
            $title = '대원 정보 삭제';
            $message = '대원 정보 삭제 에러 - 확인 후 다시 시도해 주세요';
            return $view->render($response, 'members_error.twig', ['title'=>$title, 'message'=>$message, 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
        }
        
    })->setName('members_register_id_delete');

    // ### member add form
    $app->get('/members', function ($request, $response, $args) {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'members.twig', ['login_id' => $_SESSION['userID'], 'login_name' => Login::getLoginName($_SESSION['userID'])]);
    })->setName('members');

    $app->post('/members/add', function ($request, $response, $args) {
        $view = Twig::fromRequest($request);

        require_once __DIR__ . '/src/models/Member.php';
        require_once __DIR__ . '/src/models/MemberState.php';
        require_once __DIR__ . '/src/models/Part.php';
        require_once __DIR__ . '/src/models/ChurchStaff.php';
        require_once __DIR__ . '/src/models/CalvaryStaff.php';

        $memberModel = new Member();
        $result = $memberModel->addMember($_POST["member_name"], $_POST["member_part"], $_POST["member_church_staff"], $_POST["member_calvary_staff"], $_POST["member_state"]);
        $memberStateModel = new MemberState();

        if($result[0] == 0) {
            $newId = $result[1];
            $resultMemberState = $memberStateModel->addMemberState($newId, $_POST["member_state_date"], $_POST["member_state"]);
        } elseif ($result[0] == -1) {
            $newId = $result[1];
            $resultMemberState = $memberStateModel->addMemberState($newId, $_POST["member_state_date"], $_POST["member_state"]);
        } else {
        }

        return $response->withHeader('Location', '/calvary-web-2/members/'.$newId)->withStatus(302);
        
    })->setName('members_add');

    // ### member state add
    $app->post('/members/{id}/states/add', function ($request, $response, $args) {
        $view = Twig::fromRequest($request);

        $id = $args['id'];

        require_once __DIR__ . '/src/models/MemberState.php';
        $memberStateModel = new MemberState();

        $result = $memberStateModel->addMemberState($_POST['member_id'], $_POST['member_state_date'], $_POST['member_state']);
        if($result) {
            return $response->withHeader('Location', '/calvary-web-2/members/'.$id)->withStatus(302);
        } else {
            $title = '대원 상태 추가';
            $message = '대원 상태 추가 실패 - 확인 후 다시 시도해 주세요';
            return $view->render($response, 'members_error.twig', ['title'=>$title, 'message'=>$message, 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
        }
        
    })->setName('members_state_add');

    // ### member state id
    $app->get('/members/{id}/states/{state_id}', function ($request, $response, $args) {
        $view = Twig::fromRequest($request);

        $id = $args['id'];
        $stateId = $args['state_id'];

        require_once __DIR__ . '/src/models/MemberState.php';
        $memberStateModel = new MemberState();
        $ret = $memberStateModel->getMemberState($stateId);

        return $view->render($response, 'members_states.twig', ['state_info'=>$ret[0], 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
    })->setName('members_states_id');

    // ### member state id edit
    $app->post('/members/{id}/states/{state_id}/edit', function ($request, $response, $args) {
        $view = Twig::fromRequest($request);

        $id = $args['id'];
        $stateId = $args['state_id'];

        require_once __DIR__ . '/src/models/MemberState.php';
        require_once __DIR__ . '/src/models/Member.php';
        $memberStateModel = new MemberState();
        $memberModel = new Member();

        $result = $memberStateModel->updateMemberState($stateId, $id, $_POST['member_state_date'], $_POST['member_state']);
        if($result) {
            $result = $memberModel->updateMemberLastState($id);
            if($result) {
                return $response->withHeader('Location', '/calvary-web-2/members/'.$id)->withStatus(302);
            } else {
                $title = '대원 상태 수정';
                $message = '대원 최종 상태 수정 실패 - 확인 후 다시 시도해 주세요';
                return $view->render($response, 'members_error.twig', ['title'=>$title, 'message'=>$message, 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
            }
        } else {
            $title = '대원 상태 수정';
            $message = '대원 상태 수정 실패 - 확인 후 다시 시도해 주세요';
            return $view->render($response, 'members_error.twig', ['title'=>$title, 'message'=>$message, 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
        }
    })->setName('members_state_add');

    // ### member state id delete
    $app->get('/members/{id}/states/{state_id}/delete', function ($request, $response, $args) {
        $view = Twig::fromRequest($request);

        $id = $args['id'];
        $stateId = $args['state_id'];

        require_once __DIR__ . '/src/models/MemberState.php';
        require_once __DIR__ . '/src/models/Member.php';
        $memberStateModel = new MemberState();
        $memberModel = new Member();
        $result = $memberStateModel->deleteMemberStateBySN($stateId);
        if($result) {
            $result = $memberModel->updateMemberLastState($id);
            if($result) {
                return $response->withHeader('Location', '/calvary-web-2/members/'.$id)->withStatus(302);
            } else {
                $title = '대원 상태 삭제';
                $message = '대원 최종 상태 수정 실패 - 확인 후 다시 시도해 주세요';
                return $view->render($response, 'members_error.twig', ['title'=>$title, 'message'=>$message, 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
            }
        } else {
            $title = '대원 상태 삭제';
            $message = '대원 상태 삭제 실패 - 확인 후 다시 시도해 주세요';
            return $view->render($response, 'members_error.twig', ['title'=>$title, 'message'=>$message, 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
        }
    })->setName('members_state_delete');

    $app->run();
