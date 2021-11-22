<?php
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;

	//Load phpmailer
	require 'vendor/autoload.php';

	include 'includes/session.php';

	if(isset($_POST['reset'])){
		$email = $_POST['email'];

		$conn = $pdo->open();

		$stmt = $conn->prepare("SELECT *, COUNT(*) AS numrows FROM users WHERE email=:email");
		$stmt->execute(['email'=>$email]);
		$row = $stmt->fetch();

		if($row['numrows'] > 0){
			//generate code
			$set='123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$code=substr(str_shuffle($set), 0, 15);
			try{
				$stmt = $conn->prepare("UPDATE users SET reset_code=:code WHERE id=:id");
				$stmt->execute(['code'=>$code, 'id'=>$row['id']]);
				
				$message = "
					<h2>Password Reset</h2>
					<p>Your Account:</p>
					<p>Email: ".$email."</p>
					<p>Please click the link below to reset your password.</p>
					<a href='http://localhost/project/password_reset.php?code=".$code."&user=".$row['id']."'>Reset Password</a>
				";


	    		$mail = new PHPMailer(true);                             
			    try {
			        //Server settings
			        $mail -> IsSMTP();
					$mail -> SMTPDebug = 1;
					$mail -> SMTPAuth = true;
					$mail -> SMTPSecure = 'TLS';
					$mail -> Host = "smtp.gmail.com";
					$mail -> Port = 587;
					$mail -> ISHtml(true);
					$mail -> CharSet = 'UTF-8';
					$mail -> Username = 'shoppinggenz@gmail.com';
					$mail -> Password = 'Nikhil#123';
					$mail -> SetFrom("shoppinggenz@gmail.com");
					$mail -> Subject = "Password Reset";
					$mail -> Body =$message;
					$mail -> AddAddress($email);
						

					if($mail->Send()) {
						echo '<script>alert("Please Check Your Email for Password Reset")</script>';
						$_SESSION['success'] = "Success! Check email to change password.";
						header('location: login.php');
					}
					else {
						$_SESSION['error'] = $mail->ErrorInfo;
						header("location: login.php");
					}
			     
			    } 
			    catch (Exception $e) {
			        $_SESSION['error'] = 'Message could not be sent. Mailer Error: '.$mail->ErrorInfo;
			    }
			}
			catch(PDOException $e){
				$_SESSION['error'] = $e->getMessage();
			}
		}
		else{
			$_SESSION['error'] = 'Email not found';
		}

		$pdo->close();

	}
	else{
		$_SESSION['error'] = 'Input email associated with account';
	}

	header('location: password_forgot.php');

?>