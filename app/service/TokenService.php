<?php
    namespace App\Service;
    use Firebase\JWT\JWT;

    require_once  "../vendor/firebase/php-jwt/src/BeforeValidException.php";
    require_once  "../vendor/firebase/php-jwt/src/ExpiredException.php";
    require_once  "../vendor/firebase/php-jwt/src/SignatureInvalidException.php";
    require_once  "../vendor/firebase/php-jwt/src/JWT.php";

    /**
     * Created by Yishi Lu.
     * User: Yishi Lu
     * Date: 2020/01/25
     */
    class TokenService {

        /**
         * validate a token from user cookie
         *
         * @param null
         * @return array
         */
        public function validateToken(){

            if(array_key_exists('access_token', $_COOKIE)){

                $token = $_COOKIE['access_token'];
         
                try {
                    $decoded = JWT::decode($token, "top_secret", array('HS256'));
                
                    http_response_code(200);
                
                    return array(
                        "result" => true,
                        "message" => "Access granted.",
                        "data" => $decoded->data
                    );
                
                }
                catch (Exception $e){
                
                    http_response_code(401);
                
                    return array(
                        "result" => false,
                        "message" => "Access denied.",
                        "error" => $e->getMessage()
                    );
                }
            }
            else {
                http_response_code(401);
                
                return array(
                    "result" => false, 
                    "error" => "Access denied."
                );
            }
        }
    }


?>
