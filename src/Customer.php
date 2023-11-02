<?php
namespace Src;
require_once "encryption.php";
class Customer {
  private $db;
  private $requestMethod;
  private $postId;

  public function __construct($db, $requestMethod, $postId){

    $this->db = $db;
    $this->requestMethod = $requestMethod;
    $this->postId = $postId;
  
  }

  // Main Function to handle all requests 
  public function processRequest(){

    switch ($this->requestMethod) {
      case 'GET':
        if ($this->postId) {
          $response = $this->getSingleCustomer($this->postId);
        } else {
          $response = $this->getAllCustomers();
        };
        break;
      case 'POST':
        $response = $this->addCustomer();
        break;
      case 'PUT':
        $response = $this->updateCustomer($this->postId);
        break;
      case 'DELETE':
        $response = $this->deleteCustomer($this->postId);
        break;
      default:
        $response = $this->notFoundResponse();
        break;
    }

    header($response['status_code_header']);
    if ($response['body']) {
      echo $response['body'];
    }
  }




  ###################################################################################
  #                                                                                 #
  #                   Inidvidual Functions for Different Requests                   #
  #                                                                                 #
  ###################################################################################

  private function getAllCustomers() {
    $query = "SELECT * FROM customer";

    try {
        $result = $this->db->query($query);

        if ($result) {
            $customers = $result->fetch_all(MYSQLI_ASSOC);
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($customers);
            return $response;
        } else {
            $response['status_code_header'] = 'HTTP/1.1 500 Internal Server Error';
            $response['body'] = json_encode(array('error' => 'An error occurred while fetching customers.'));
            return $response;
        }
    } catch (\Exception $e) {
        exit($e->getMessage());
    }
  }

  private function getSingleCustomer($id){

    $result = $this->getCustomerById($id);
    if (! $result) {
      return $this->notFoundResponse();
    }
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode($result);
    return $response;
  }


  function addCustomer(){

    $input = (array) json_decode(file_get_contents('php://input'), TRUE);

    if (empty($input)) { //For form data send in Array Format
      // $input = $_POST;
      $firstName    = $this->db->real_escape_string($_POST['firstName']);
      $lastName     = $this->db->real_escape_string($_POST['lastName']);
      $email        = $this->db->real_escape_string($_POST['email']);
      $password     = $this->db->real_escape_string($_POST['password']);
      $password_cnf = $this->db->real_escape_string($_POST['password_cnf']);
      $country      = $this->db->real_escape_string($_POST['country']);
      $profession   = $this->db->real_escape_string($_POST['profession']);
      $addedOn      = $this->db->real_escape_string($_POST['added_on']);

    }else { //For raw data send in JSON format
      // $input = $input;
      $firstName    = $this->db->real_escape_string($input['firstName']);
      $lastName     = $this->db->real_escape_string($input['lastName']);
      $email        = $this->db->real_escape_string($input['email']);
      $password     = $this->db->real_escape_string($input['password']);
      $password_cnf = $this->db->real_escape_string($input['password_cnf']);
      $country      = $this->db->real_escape_string($input['country']);
      $profession   = $this->db->real_escape_string($input['profession']);
      $addedOn      = $this->db->real_escape_string($_POST['added_on']);
    }
    
    if (empty($firstName)) {
      $errMsg = 'First name is empty!';
    }elseif (empty($lastName)) {
      $errMsg = 'Last name is empty!';
    }elseif (empty($email)) {
      $errMsg = 'Email is empty!';
    }elseif (empty($password)) {
      $errMsg = 'Password is empty!';
    }elseif (empty($password_cnf)) {
      $errMsg = 'Confirm password is empty!';
    }elseif (empty($country)) {
      $errMsg = 'Country is empty!';
    }elseif (empty($profession)) {
      $errMsg = 'Profession is empty!';
    }elseif ($password != $password_cnf) {
      $errMsg = 'Confirm password not matched!';
    }else {

      if ($this->userExists($email)) {
        return $this->unprocessableEntityResponse('Email Already Exists!');
      }

      $client   = 1;
      $seller   = 2;
      $cusType = $client;

      $userName			    = $email;
      $discountOffered  = 0;
      $accVerified      = 'N';
      $sortOrder        = 0;
      $featured         = 'Y';
      $status           = 1;


      $uniqueId         = time().' '.mt_rand();
      $uniqueId         = md5($uniqueId);
      $verificationNo   = $uniqueId;

      
      //Inserting in customer table
      $x_password = md5_encrypt($password, USER_PASS);

      try {
        
        $sql	 = 	"INSERT INTO customer 
                (customer_type, user_name, email, password, fname, lname, status, featured, profession, sort_order, 
                acc_verified, verification_no, discount_offered)
                VALUES
                ('$cusType', '$userName', '$email', '$x_password', '$firstName', '$lastName', '$status', '$featured', '$profession', '$sortOrder', 
                '$accVerified', '$verificationNo', '$discountOffered')";
                
        // execute query
        $this->db->query($sql);

        //get the primary key
        $customerId		= 	$this->db->insert_id;

        //inserting into customer info table
        $sql2	=   "INSERT INTO customer_info  (last_logon, no_logon, added_on, customer_id)
                    VALUES
                  (now(), 1, now(), '$customerId')";
        $this->db->query($sql2);
        
        
        $sql3 = "INSERT INTO `customer_address`(`customer_id`) VALUES ('$customerId')";
        $this->db->query($sql3);

        $message = json_encode(array('status' => 201, 'message' => 'Customer added successfuly!', 'customer_id'=> $customerId, 'fname' => $firstName, 'lname' => $lastName, 'email' => $email, 'verification_key' => $verificationNo));

      } catch (\Exception $e) {
        $message = json_encode(array('status' => 0, 'error' => $e->getMessage()));
      }

      $response['status_code_header'] = 'HTTP/1.1 201 Inserted';
      $response['body'] = $message;
      return $response;

    }
    $response['status_code_header'] = 'HTTP/1.1 500 Internal Server Error';
    $response['body'] = json_encode(array('status' => 0, 'error' => $errMsg));		
    return $response;

	}//eof

  

  // function addCusAddress($customer_id, $address1, $address2, $address3, $town, $province, $postal_code, $countries_id, 
	// 						$phone1, $phone2, $fax, $mobile){

	// 	$customer_id	= addslashes(trim($customer_id)); 
	// 	$address1		= addslashes(trim($address1));  
	// 	$town			= addslashes(trim($town)); 
	// 	$province		= addslashes(trim($province)); 
	// 	$postal_code	= addslashes(trim($postal_code));
	// 	$phone1			= addslashes(trim($phone1)); 
	// 	$fax			= addslashes(trim($fax)); 
		
	// 	$sql 	= "INSERT INTO customer_address
	// 			  (customer_id, address1, address2, address3, town, province, postal_code, countries_id, phone1, phone2, fax, mobile)
	// 			  VALUES
	// 			  ($customer_id, '$address1','$address2', '$address3', '$town', '$province', '$postal_code', '$countries_id', '$phone1', 
	// 			  '$phone2', '$fax', '$mobile')";
		
	// 	//echo $sql.mysql_error();exit;
	// 	//execute query		  
	// 	$query	= $this->db->query($sql);
		
		
	// 	$result = '';
	// 	if(!$query){
	// 		$result = 'ER101';
	// 	}else{
	// 		$result = 'SU101';
	// 	}
	// 	return $result;
	// }//eof


  
  private function updateCustomer($id){

  //   $result = $this->getCustomerById($id);

  //   if (! $result) {
  //     return $this->notFoundResponse();
  //   }

  //   $input = (array) json_decode(file_get_contents('php://input'), TRUE);

  //   if (! $this->validatePost($input)) {
  //     return $this->unprocessableEntityResponse();
  //   }

  //   $statement = "
  //     UPDATE post
  //     SET
  //       title = :title,
  //       body  = :body,
  //       author = :author,
  //       author_picture = :author_picture
  //     WHERE id = :id;
  //   ";

  //   try {
  //     $statement = $this->db->prepare($statement);
  //     $statement->execute(array(
  //       'id' => (int) $id,
  //       'title' => $input['title'],
  //       'body'  => $input['body'],
  //       'author' => $input['author'],
  //       'author_picture' => 'https://secure.gravatar.com/avatar/'.md5($input['author']).'.png?s=200',
  //     ));
  //     $statement->rowCount();
  //   } catch (\PDOException $e) {
  //     exit($e->getMessage());
  //   }
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode(array('message' => 'Post Updated!'));
    return $response;
  }


  //================== Delete a customer by customer_id ==================//
  private function deleteCustomer($customer_id) {
    $result = $this->getCustomerById($customer_id);

    if (!$result) {
        return $this->notFoundResponse();
    }

    // Delete the customer's related records from 'customer_address' and 'customer_info' tables
    $query = "DELETE FROM customer WHERE customer_id = ?";
    $queryAddress = "DELETE FROM customer_address WHERE customer_id = ?";
    $queryInfo = "DELETE FROM customer_info WHERE customer_id = ?";
    
    try {
        $statement = $this->db->prepare($query);
        $statement->bind_param("s", $customer_id);
        $statement->execute();
        $rowDeleted = $statement->affected_rows;

        $statementAddress = $this->db->prepare($queryAddress);
        $statementAddress->bind_param("s", $customer_id);
        $statementAddress->execute();
        $rowsDeletedAddress = $statementAddress->affected_rows;
            
        $statementInfo = $this->db->prepare($queryInfo);
        $statementInfo->bind_param("s", $customer_id);
        $statementInfo->execute();
        $rowsDeletedInfo = $statementInfo->affected_rows;

          // if ($rowDeleted > 0) {
          //     $response['status_code_header'] = 'HTTP/1.1 200 OK';
          //     $response['body'] = json_encode(array('message' => 'Customer Deleted!'));
          // } else {
          //     $response['status_code_header'] = 'HTTP/1.1 500 Internal Server Error';
          //     $response['body'] = json_encode(array('error' => 'An error occurred while deleting the customer.'));
          // }

        if ($rowDeleted > 0 && $rowsDeletedAddress > 0 && $rowsDeletedInfo > 0) {

            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode(array('status' => 0, 'message' => 'Customer and related records deleted.'));

        } elseif ($rowDeleted > 0 && $rowsDeletedAddress > 0 && $rowsDeletedInfo <= 0) {

            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode(array('status' => 0, 'message' => 'Customer and address are deleted, but related records not deleted.'));

        } elseif ($rowDeleted > 0 && $rowsDeletedAddress <= 0 && $rowsDeletedInfo > 0) {

            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode(array('status' => 0, 'message' => 'Customer deleted, but address records not deleted.'));

        }elseif ($rowDeleted <= 0 && $rowsDeletedAddress > 0 && $rowsDeletedInfo > 0) {

            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode(array('status' => 0, 'message' => 'Customer address and info deleted, but credentials not deleted.'));

        } elseif ($rowDeleted <= 0 && $rowsDeletedAddress <= 0 && $rowsDeletedInfo > 0) {

            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode(array('status' => 0, 'message' => 'Customer info deleted, but related records not deleted.'));

        } elseif ($rowDeleted > 0 && $rowsDeletedAddress <= 0 && $rowsDeletedInfo <= 0) {

          $response['status_code_header'] = 'HTTP/1.1 200 OK';
          $response['body'] = json_encode(array('status' => 0, 'message' => 'Customer deleted, but related records not deleted.'));

        } else {
            $response['status_code_header'] = 'HTTP/1.1 500 Internal Server Error';
            $response['body'] = json_encode(array('status' => 0, 'error' => 'An error occurred while deleting the customer or related records.'));
        }
        
      } catch (\Exception $e) {
          
        $response['status_code_header'] = 'HTTP/1.1 500 Internal Server Error';
        $response['body'] = json_encode(array('status' => 0, 'error' => $e->getMessage()));
          
      }
      return $response;
    }
 

  //================== Get a customer detsils by customer_id ==================//
  public function getCustomerById($customer_id) {
    try {
      
      $query = "SELECT * FROM customer WHERE customer_id = ? ";
      $statement = $this->db->prepare($query);
      $statement->bind_param("s", $customer_id);
      $statement->execute();
      $result = $statement->get_result()->fetch_assoc();
      return $result;

    } catch (\Exception $e) {
        exit($e->getMessage());
    }
  }


  /**
	*	This function will check whether the username is already in use or not
	*
	*	@param
	*			$id 			User name or id
	*			$fieldName		Column Name
	*			$tableName 		Table to make query
	*
	*	@return string
	*/
	public function userExists($value) {
    $query = "SELECT COUNT(*) AS count FROM customer WHERE customer_id = ? OR email = ? OR user_name = ?";
    
    try {
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("sss", $value, $value, $value);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        // Check if any rows matched the query
        if ($row['count'] > 0) {
            return true; // User exists
        } else {
            return false; // User does not exist
        }
    } catch (\Exception $e) {
        exit($e->getMessage());
    }
  }



  private function unprocessableEntityResponse($msg = 'Invalid input'){

    $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
    $response['body'] = json_encode(['status' => 0, 'error' => $msg]);
    return $response;
  }

  private function notFoundResponse(){

    $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
    $response['body'] = null;
    return $response;
  }
}