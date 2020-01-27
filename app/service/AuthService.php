<?php
    namespace App\Service;
    use App\Utility\Database;
    use App\Model\User;
    use \Firebase\JWT\JWT;

    require_once  "../utility/db.php";
    require_once  "../model/User.php";
    require_once  "../vendor/firebase/php-jwt/src/BeforeValidException.php";
    require_once  "../vendor/firebase/php-jwt/src/ExpiredException.php";
    require_once  "../vendor/firebase/php-jwt/src/SignatureInvalidException.php";
    require_once  "../vendor/firebase/php-jwt/src/JWT.php";

    /**
     * Created by Yishi Lu.
     * User: Yishi Lu
     * Date: 2020/01/25
     */
    class AuthService{
    
        private $conn;

        public function __construct(){

            $database = new Database();
            $this->conn = $database->getConnection();
        }

        /**
         * register new user with given credentials
         *
         * @param null
         * @return array
         */
        public function register(){

            $user = new User();

            $user->setName($_POST['name'] ?? null);
            $user->setPassword($_POST['password'] ?? null);
            $user->setConfirmPassword($_POST['confirm_password'] ?? null);
            $user->setEmail($_POST['email'] ?? null);

            if(!empty($user->getName()) && !empty($user->getPassword()) && !empty($user->getConfirmPassword()) && !empty($user->getEmail())){

                if(strlen($user->getPassword()) < 8) return array("error" => "password must be at least 8 characters");

                if (!filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) return array("error" => "invalid email address");

                if($user->getPassword() == $user->getConfirmPassword()){

                    $stmt = $this->conn->prepare("Select * from User Where name=? Or email=?");
                    $stmt->bind_param("ss", $name, $email);

                    $name = $user->getName();
                    $email = $user->getEmail();

                    $stmt->execute();

                    $result = $stmt->get_result();

                    if($result->num_rows == 0 ) {

                        $stmt = $this->conn->prepare("INSERT INTO User (name, password, email) VALUES (?, ?, ?)");
                        $stmt->bind_param("sss", $name, $password_hash, $email);

                        $name = htmlspecialchars(strip_tags($user->getName()));
                        $email = htmlspecialchars(strip_tags($user->getEmail()));
                        $password = htmlspecialchars(strip_tags($user->getPassword()));

                        $password_hash = password_hash($password, PASSWORD_DEFAULT);

                        $stmt->execute();
                        $stmt->close();

                        return array("success"=>"user created");

                    }
                    else return array("error"=>"exist user name or email");

                }
                else return array("error" => "password not same");

            }
            else return array("error" => "empty fields");
        }

        /**
         * login existed user with given credentials
         *
         * @param null
         * @return array
         */
        public function login(){

            $user = new User();

            $user->setName($_POST['name'] ?? null);
            $user->setPassword($_POST['password'] ?? null);

            if(!empty($user->getName()) && !empty($user->getPassword())){

                $stmt = $this->conn->prepare("Select * from User Where name=?");
                $stmt->bind_param("s", $name);

                $name = htmlspecialchars(strip_tags($user->getName()));
                
                $stmt->execute();
                $result = $stmt->get_result();

                if($row = $result->fetch_assoc()) {
                    $cur_user['id'] = $row['id'];
                    $cur_user['name'] = $row['name'];
                    $cur_user['email'] = $row['email'];
                    $password_hash = $row['password'];
                }

                $password = htmlspecialchars(strip_tags($user->getPassword())) ?? null;

                $stmt->close();

                if (password_verify($password, $password_hash ?? null)) {
                    return array("success"=>$cur_user);
                }
                else {
                    return array("error"=>"invalid username/password");
                }
            }
            else return array("error" => "empty field");
        }
    }
?>