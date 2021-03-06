<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

require '../includes/usersControllers.php';
require '../includes/barangControllers.php';

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
    
    if(!haveEmptyParameters(array('email','password','nama','area','alamat'), $request, $response)){
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
    
    if(!haveEmptyParameters(array('email','password'), $request, $response)){
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

$app->put('/updateUser/{id_user}', function (Request $request, Response $response, array $args){

    $id_user = $args['id_user'];

    if(!haveEmptyParameters(array('email', 'nama', 'area', 'alamat', 'id_user'), $request, $response)){
        $request_data = $request->getParsedBody();
        $email = $request_data['email'];
        $nama = $request_data['nama'];
        $area = $request_data['area'];
        $alamat = $request_data['alamat'];
        $id_user = $request_data['id_user'];

        $db = new usersControllers;

        if($db->updateUser($email, $nama, $area, $alamat, $id_user)){
            $response_data = array();
            $response_data['error'] = false;
            $response_data['message'] = 'User updated successfully';
            $user = $db->getUserByEmail($email);
            $response_data['user'] = $user;

            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Please try again later';
            $user = $db->getUserByEmail($email);
            $response_data['user'] = $user;

            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }

    }

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
});

$app->put('/updatePassword', function (Request $request, Response $response){

    if(!haveEmptyParameters(array('currentpassword', 'newpassword', 'email'), $request, $response)){
        $request_data = $request->getParsedBody();

        $currentpassword = $request_data['currentpassword'];
        $newpassword = $request_data['newpassword'];
        $email = $request_data['email'];

        $db = new usersControllers;

        $result = $db->updatePassword($currentpassword, $newpassword, $email);

        if($result == PASSWORD_CHANGED){
            $response_data = array();
            $response_data['error'] = false;
            $response_data['message'] = 'Password changed';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if($result == PASSWORD_DO_NOT_MATCH) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'You have given wrong password';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if($result == PASSWORD_DO_NOT_CHANGED){
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Some error occurred';
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

$app->delete('/deleteUser/{id_user}', function(Request $request, Response $response, array $args){

    $id_user = $args['id_user'];
    
    $db = new usersControllers;

    $response_data = array();

    if($db->deleteUser($id_user)){
        $response_data['error'] = false;
        $response_data['message'] = 'User has been deleted';
    } else {
        $response_data['error'] = true;
        $response_data['message'] = 'Please try again later';
    }

    $response->write(json_encode($response_data));

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
});

$app->post('/createBarang', function(Request $request, Response $response){
    $request_data = $request->getParsedBody();

    $id_barang = $request_data['id_barang'];
    $nama_barang = $request_data['nama_barang'];
    $detail = $request_data['detail'];
    $harga = $request_data['harga'];
    $stok_barang = $request_data['stok_barang'];

    $db = new barangControllers;

    $result = $db->createBarang($id_barang, $nama_barang, $detail, $harga, $stok_barang);

    if($result == BARANG_CREATED){

        $message = array();
        $message['error'] = false;
        $message['message'] = 'Data barang tersimpan';

        $response->write(json_encode($message));

        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200);

    } else if($result == BARANG_FAILURE){
        
        $message = array();
        $message['error'] = true;
        $message['message'] = 'Some error occurred';

        $response->write(json_encode($message));

        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(422);

    } else if($result == BARANG_EXISTS){

        $message = array();
        $message['error'] = true;
        $message['message'] = 'Barang telah ada';

        $response->write(json_encode($message));

        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(422);

    }

    $response->write(json_encode($message));

        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(422);
});

$app->get('/allBarang', function(Request $request, Response $response){

    $db = new barangControllers;

    $barangs = $db->getAllBarang();

    $response_data = array();

    $response_data['error'] = false;
    $response_data['barangs'] = $barangs;

    $response->write(json_encode($response_data));

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
});

$app->delete('/deleteBarang/{id_barang}', function(Request $request, Response $response, array $args){

    $id_barang = $args['id_barang'];

    $db = new barangControllers;

    if($db->deleteBarang($id_barang)){
        $response_data['error'] = false;
        $response_data['message'] = 'Barang telah dihapus';
    } else {
        $response_data['error'] = true;
        $response_data['message'] = 'Please try again later';
    }

    $response->write(json_encode($response_data));

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
});

function haveEmptyParameters($required_params, $request, $response){
    $error = false;
    $error_params = '';
    $request_params = $request->getParsedBody();

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