<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

require '../includes/usersControllers.php';

$app = new \Slim\App([
    'settings' =>[
        'displayErrorDetails'=> true
    ]
]);

/*
    endpoint : createUser
    parameters : email, password, nama, area, alamat
    method : POST
*/

$app->post('/createUser',function(Request $request, Response $response){
    
    if(!haveEmptyParameters(array('email','password','nama','area','alamat'), $response)){
        $request_data = $request->getParsedBody();

        $email = $request_data['email'];
        $password = $request_data['password'];
        $nama = $request_data['nama'];
        $area = $request_data['area'];
        $alamat = $request_data['alamat'];

        $hash_password = password_hash($password, PASSWORD_DEFAULT);

        $db = new usersControllers;

        $result = $db->createUser($email,$hash_password,$nama,$area,$alamat);

        if($result == USER_CREATED){

            $message = array();
            $message['error'] = false;
            $message['message'] = 'User Created Successfully';

            $response->write(json_encode($message));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(201);

        } else if($result == USER_FAILURE){

            $message = array();
            $message['error'] = true;
            $message['message'] = 'Some error occurred';

            $response->write(json_encode($message));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(422);

        } else if($result == USER_EXISTS){

            $message = array();
            $message['error'] = true;
            $message['message'] = 'User Already Exists';

            $response->write(json_encode($message));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(422);

        }
    }

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);
});

$app->post('/userLogin',function(Request $request, Response $response){
    
    if(!haveEmptyParameters(array('email','password'),$response)){
        $request_data = $request->getParsedBody();

        $email = $request_data['email'];
        $password = $request_data['password'];

        $db = new usersControllers;

        $result = $db->userLogin($email,$password);

        if($result == USER_AUTHENTICATED){

            $user = $db->getUserByEmail($email);
            $response_data = array();

            $response_data['error'] = false;
            $response_data['message'] = 'Login Successfull';
            $response_data['user'] = $user;

            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);

        } else if($result == USER_NOT_FOUND){

            $response_data = array();

            $response_data['error'] = true;
            $response_data['message'] = 'User not exist';

            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);

        } else if($result == USER_PASSWORD_DO_NOT_MATCH){

            $response_data = array();

            $response_data['error'] = true;
            $response_data['message'] = 'Invalid credential';

            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
                
        }
    }

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);
});

$app->get('/allUsers', function(Request $request, Response $response){

    $db = new usersControllers;
    
    $users = $db->getAllUsers();

    $response_data = array();

    $response_data['error'] = false;
    $response_data['users'] = $users;

    $response->write(json_encode($response_data));

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
});

function haveEmptyParameters($required_params, $response){
    $error = false;
    $error_params = '';
    $request_params = $_REQUEST;

    foreach($required_params as $param){
        if(!isset($request_params[$param]) || strlen($request_params[$param]) <= 0){
            $error = true;
            $error_params .= $param . ', ';

        }
    }

    if($error){
        $error_detail = array();
        $error_detail['error'] = true;
        $error_detail['message'] = 'Required parameters '. substr($error_params, 0, -2) . ' are missing or empty';
        $response->write(json_encode($error_detail));
    }
    return $error;
}
$app->run();