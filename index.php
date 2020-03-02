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
            require_once __DIR__ . '/src/models/Part.php';
            $partNum = Part::getPartNumberByLoginId($_SESSION['userID']);
            $today = date('Ymd');
            if($partNum>0) {
                // part menu
                return $view->render($response, 'home.twig', ['part_num'=>$partNum, 'date'=>$today, 'login_id' => $_SESSION['userID'], 'login_name' => Login::getLoginName($_SESSION['userID'])]);
            } else {
                return $view->render($response, 'home.twig', ['login_id' => $_SESSION['userID'], 'login_name' => Login::getLoginName($_SESSION['userID'])]);
            }
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
                return $view->render($response, 'result_error.twig', ['title'=>$title, 'message'=>$message, 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
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
                return $view->render($response, 'result_error.twig', ['title'=>$title, 'message'=>$message, 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
            }
        } else {
            $title = '대원 정보 조회';
            $message = '입력된 아이디 값 에러 - 확인 후 다시 시도해 주세요';
            return $view->render($response, 'result_error.twig', ['title'=>$title, 'message'=>$message, 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
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
                return $view->render($response, 'result_error.twig', ['title'=>$title, 'message'=>$message, 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
            }
        } else {
            $title = '대원 정보 수정';
            $message = '이름 입력값 에러 - 확인 후 다시 시도해 주세요';
            return $view->render($response, 'result_error.twig', ['title'=>$title, 'message'=>$message, 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
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
            return $view->render($response, 'result_success.twig', ['title'=>$title, 'message'=>$message, 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
            } else {
                $title = '대원 정보 삭제';
            $message = '대원 상태 정보 삭제 에러 - 확인 후 다시 시도해 주세요';
            return $view->render($response, 'result_error.twig', ['title'=>$title, 'message'=>$message, 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
            }
        } else {
            $title = '대원 정보 삭제';
            $message = '대원 정보 삭제 에러 - 확인 후 다시 시도해 주세요';
            return $view->render($response, 'result_error.twig', ['title'=>$title, 'message'=>$message, 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
        }
        
    })->setName('members_id_delete');

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
            return $view->render($response, 'result_error.twig', ['title'=>$title, 'message'=>$message, 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
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
                return $view->render($response, 'result_error.twig', ['title'=>$title, 'message'=>$message, 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
            }
        } else {
            $title = '대원 상태 수정';
            $message = '대원 상태 수정 실패 - 확인 후 다시 시도해 주세요';
            return $view->render($response, 'result_error.twig', ['title'=>$title, 'message'=>$message, 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
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
                return $view->render($response, 'result_error.twig', ['title'=>$title, 'message'=>$message, 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
            }
        } else {
            $title = '대원 상태 삭제';
            $message = '대원 상태 삭제 실패 - 확인 후 다시 시도해 주세요';
            return $view->render($response, 'result_error.twig', ['title'=>$title, 'message'=>$message, 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
        }
    })->setName('members_state_delete');

    // ### attendence entry date add form
    $app->get('/attendences/entrydate', function ($request, $response, $args) {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'attendences_entrydate.twig', ['login_id' => $_SESSION['userID'], 'login_name' => Login::getLoginName($_SESSION['userID'])]);
    })->setName('entrydate');

     //  ## attendence entry date search
    $app->get('/attendences/entrydate/search', function ($request, $response, $args) {
        $view = Twig::fromRequest($request);
        if(isset($_GET['search'])) {

            $inputInfo['start'] = $_GET["start"];
            $inputInfo['end'] = $_GET["end"];

            require_once __DIR__ . '/src/models/EntryDate.php';
            $ed = new EntryDate();
            $searchResult = $ed->getEntryDateSearchResult($inputInfo['start'], $inputInfo['end']);

            if(count($searchResult) > 0) {
                //
                for($rowIdx=0; $rowIdx<count($searchResult); $rowIdx++) {
                    $searchResult[$rowIdx]['type'] = EntryDate::getEntryDateDay($searchResult[$rowIdx]['type']);
                }
                $result = 'success';
            } else {
                $result = 'fail';
            }
            return $view->render($response, 'attendences_entrydate_search.twig', ['result'=>$result, 'input_info'=>$inputInfo, 'search_result'=>$searchResult, 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);

        } else {
            return $view->render($response, 'members_search.twig', ['login_id' => $_SESSION['userID'], 'login_name' => Login::getLoginName($_SESSION['userID'])]);
        }
    })->setName('entrydate_search');

    // ### attendence entry date add
    $app->post('/attendences/entrydate/add', function ($request, $response, $args) {
        $view = Twig::fromRequest($request);

        require_once __DIR__ . '/src/models/EntryDate.php';
        $ed = new EntryDate();
        $result = $ed->addEntryDate($_POST['date'], $_POST['description']);
        if($result>0) {
            return $response->withHeader('Location', '/calvary-web-2/attendences/entrydate/'.$result)->withStatus(302);
        } else {
            $title = '출결 날짜 추가';
            $message = '출결 날짜 추가 실패 - 확인 후 다시 시도해 주세요';
            return $view->render($response, 'result_error.twig', ['title'=>$title, 'message'=>$message, 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
        }
        
    })->setName('entrydate_add');

    // ### attendences entry date id
    $app->get('/attendences/entrydate/{id}', function ($request, $response, $args) {
        $view = Twig::fromRequest($request);

        $id = $args['id'];
        if(is_numeric($id)) {
            require_once __DIR__ . '/src/models/EntryDate.php';
            $ed = new EntryDate();
            $searchResult = $ed->getEntryDateBySN($id);
            if(count($searchResult) == 1) {
                $info = array();
                $info['sn'] = $searchResult[0]['sn'];
                $info['date'] = $searchResult[0]['att_date'];
                $info['type'] = EntryDate::getEntryDateDay($searchResult[0]['type']);
                $info['details'] = $searchResult[0]['details'];
                return $view->render($response, 'attendences_entrydate_id.twig', ['info'=>$info, 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
            } else {
                $title = '출결 날짜 조회';
                $message = '출결 날짜 조회 실패 - 확인 후 다시 시도해 주세요';
                return $view->render($response, 'result_error.twig', ['title'=>$title, 'message'=>$message, 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
            }
        } else {
            $title = '출결 날짜 조회';
            $message = '입력된 아이디 값 에러 - 확인 후 다시 시도해 주세요';
            return $view->render($response, 'result_error.twig', ['title'=>$title, 'message'=>$message, 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
        }
        
    })->setName('entrydate_id');

    // ### attendence entry date id edit
    $app->post('/attendences/entrydate/{id}/edit', function ($request, $response, $args) {
        $view = Twig::fromRequest($request);

        $id = $args['id'];
        require_once __DIR__ . '/src/models/EntryDate.php';
        $ed = new EntryDate();
        
        $editResult = $ed->updateEntryDateDescription($id, $_POST["description"]);
        if($editResult) {
            return $response->withHeader('Location', '/calvary-web-2/attendences/entrydate/'.$id)->withStatus(302);
        } else {
            $title = '출결 날짜 정보 수정';
            $message = '출결 날짜 정보 수정 실패 - 확인 후 다시 시도해 주세요';
            return $view->render($response, 'result_error.twig', ['title'=>$title, 'message'=>$message, 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
        }
        
    })->setName('entrydate_id_edit');

    // ### attendence entry date id delete
    $app->get('/attendences/entrydate/{id}/delete', function ($request, $response, $args) {
        $view = Twig::fromRequest($request);

        $id = $args['id'];
        require_once __DIR__ . '/src/models/EntryDate.php';
        $ed = new EntryDate();
        $result = $ed->deleteEntryDate($id);

        if($result) {
            $title = '출결 날짜 삭제';
            $message = '출결 날짜 삭제 성공 - '.$id.' 출결 날짜가 삭제 되었습니다';
            return $view->render($response, 'result_success.twig', ['title'=>$title, 'message'=>$message, 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
        } else {
            $title = '출결 날짜 삭제';
            $message = '출결 날짜 삭제 에러 - 확인 후 다시 시도해 주세요';
            return $view->render($response, 'result_error.twig', ['title'=>$title, 'message'=>$message, 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
        }
        
    })->setName('entrydate_id_delete');

    // ### part
    //  ## attendence log
    $app->get('/attendences/logs/parts/{part_num}/members/123456/{date}', function ($request, $response, $args) {
        $view = Twig::fromRequest($request);

        require_once __DIR__ . '/src/models/Functions.php';
        $partNum = $args['part_num'];
        $entryDate = Functions::getFormatDate($args['date']);
        require_once __DIR__ . '/src/models/Part.php';
        $loginPartNum = Part::getPartNumberByLoginId($_SESSION['userID']);

        if($partNum == $loginPartNum) {

            require_once __DIR__ . '/src/models/EntryDate.php';
            $ed = new EntryDate();
            $edCheck = $ed->checkEntryDate($args['date']);
            if($edCheck) {
                $attData = array();

                require_once __DIR__ . '/src/models/MemberState.php';
                require_once __DIR__ . '/src/models/Attendence.php';
                $part = new Part($partNum);
                $memberList = $part->getPartAttendenceMemberList($entryDate);
                $att = new Attendence();
                $attLog = $att->getAttLog($partNum, $entryDate);

                $timestamp = strtotime($entryDate);
                $m1Timestamp = strtotime(date('Y-m-01', $timestamp).' -1 months');
                $m2Timestamp = strtotime(date('Y-m-01', $timestamp).' -2 months');
                $m3Timestamp = strtotime(date('Y-m-01', $timestamp).' -3 months');
                $m1 = date('Y-m-d', $m1Timestamp);
                $m2 = date('Y-m-d', $m2Timestamp);
                $m3 = date('Y-m-d', $m3Timestamp);
                $m1Label = date('n', $m1Timestamp);
                $m2Label = date('n', $m2Timestamp);
                $m3Label = date('n', $m3Timestamp);

                $attM1Log = $att->getMonthAttLog($partNum, $m1);
                $attM2Log = $att->getMonthAttLog($partNum, $m2);
                $attM3Log = $att->getMonthAttLog($partNum, $m3);
                $monthLabel = array($m1Label, $m2Label, $m3Label);

                $totalAll = 0;
                $totalLog = 0;
                $normalAll = 0;
                $normalLog = 0;
                $newbieAll = 0;
                $newbieLog = 0;

                for($rowIdx=0; $rowIdx<count($memberList); $rowIdx++) {
                    $temp['id'] = $memberList[$rowIdx]['id'];
                    $temp['name'] = $memberList[$rowIdx]['name'];
                    if($rowIdx == 0) {
                        $temp['state'] = '파트장';
                    } else {
                        if($memberList[$rowIdx]['state'] < 3) {
                            $temp['state'] = '';
                        } else {
                            $temp['state'] = MemberState::getMemberStateName($memberList[$rowIdx]['state']);
                        }
                    }
                    // attendence log
                    if(array_key_exists($memberList[$rowIdx]['id'], $attLog)) {
                        $attDateLog = $attLog[$memberList[$rowIdx]['id']];
                        $temp['att'] = $attDateLog[0];
                        $temp['late'] = $attDateLog[1];
                    } else {
                        $temp['att'] = 0;
                        $temp['late'] = 0;
                    }
                    // 3 month attendence log
                    if(array_key_exists($memberList[$rowIdx]['id'], $attM1Log)) {
                        $temp['m1'] = $attM1Log[$memberList[$rowIdx]['id']];
                    } else {
                        $temp['m1'] = -1;
                    }
                    if(array_key_exists($memberList[$rowIdx]['id'], $attM2Log)) {
                        $temp['m2'] = $attM2Log[$memberList[$rowIdx]['id']];
                    } else {
                        $temp['m2'] = -1;
                    }
                    if(array_key_exists($memberList[$rowIdx]['id'], $attM3Log)) {
                        $temp['m3'] = $attM3Log[$memberList[$rowIdx]['id']];
                    } else {
                        $temp['m3'] = -1;
                    }
                    $temp['m1_color'] = $att->getTDClass($temp['m1']);
                    $temp['m2_color'] = $att->getTDClass($temp['m2']);
                    $temp['m3_color'] = $att->getTDClass($temp['m3']);
                    // update statistics
                    $totalAll++;
                    if($temp['att'] == 10) {
                        $totalLog++;
                    }
                    if($memberList[$rowIdx]['state'] < 3) {
                        $normalAll++;
                        if($temp['att'] == 10) {
                            $normalLog++;
                        }
                    } else if($memberList[$rowIdx]['state'] == 3) {
                        $newbieAll++;
                        if($temp['att'] == 10) {
                            $newbieLog++;
                        }
                    }
                    array_push($attData, $temp);
                }
                $stat = array('tot'=>$totalAll, 'tot_log'=>$totalLog, 'nor'=>$normalAll, 'nor_log'=>$normalLog, 'new'=>$newbieAll, 'new_log'=>$newbieLog);
                if(count($memberList) > 0) {
                    return $view->render($response, 'part_attendence_log.twig', ['result'=>'success', 'stat'=>$stat, 'month_label'=>$monthLabel, 'att_data'=>$attData, 'part_num'=>$partNum, 'date'=>$args['date'], 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
                } else {
                    return $view->render($response, 'part_attendence_log.twig', ['result'=>'fail', 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
                }
            } else {
                $title = '출석 입력';
                $message = '오늘은 출석 입력일이 아닙니다';
                return $view->render($response, 'result_error.twig', ['title'=>$title, 'message'=>$message, 'part_num'=>$loginPartNum, 'date'=>$args['date'], 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
            }
        } else {
            $title = '출석 입력';
            $message = '사용 권한 오류 - 다시 로그인 해 주세요';
            return $view->render($response, 'result_error.twig', ['title'=>$title, 'message'=>$message, 'part_num'=>$loginPartNum, 'date'=>$args['date'], 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
        }
        
    })->setName('att_log');

    //  ## attendence log
    $app->post('/attendences/logs/parts/{part_num}/members/123456/{date}/edit', function ($request, $response, $args) {
        $view = Twig::fromRequest($request);

        require_once __DIR__ . '/src/models/Functions.php';
        $partNum = $args['part_num'];
        $date = $args['date'];
        $entryDate = Functions::getFormatDate($date);
        
        if(isset($_POST['update'])) {
            $att = array();
            $late = array();
            $id = $_POST['id'];
            $attInTemp = $_POST['att'];
            $lateInTemp = $_POST['late'];
            for($idx=0; $idx<count($attInTemp); $idx++){
                if($attInTemp[$idx]=='off'){
                    //
                } else {
                    array_pop($att);
                }
                array_push($att, $attInTemp[$idx]);
            }
            for($idx=0; $idx<count($lateInTemp); $idx++){
                if($lateInTemp[$idx]=='off'){
                    //
                } else {
                    array_pop($late);
                }
                array_push($late, $lateInTemp[$idx]);
            }

            require_once __DIR__ . '/src/models/Attendence.php';
            $attModel = new Attendence();
            $result = $attModel->updateAttLog($partNum, $entryDate, $id, $att, $late);
            if(count($id) == count($result)) {
                return $response->withHeader('Location', '/calvary-web-2/attendences/logs/parts/'.$partNum.'/members/123456/'.$date)->withStatus(302);
            } else {
                $title = '출석 입력';
                $message = '출석 입력 실패 - 확인 후 다시 시도해 주세요';
                return $view->render($response, 'result_error.twig', ['title'=>$title, 'message'=>$message, 'login_id'=>$_SESSION['userID'], 'login_name'=>Login::getLoginName($_SESSION['userID'])]);
            }
        } else {
            // do nothing, redirecto attendence log form
            return $response->withHeader('Location', '/calvary-web-2/attendences/logs/parts/'.$partNum.'/members/123456/'.$date)->withStatus(302);
        }
        
        
    })->setName('att_log_edit');

    $app->run();
