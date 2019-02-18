<!DOCTYPE html>
<html lang="en">
<head>
	<title>Login</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="css/style.css">
	<script type="text/javascript" src="http://code.jquery.com/jquery-1.7.1.min.js"></script>
</head>
<body>

<div class="login-page">
  <div class="form">
    <form class="register-form" >
      <p class="warning" id="warning_msg"></p>
      <input type="text" id="reg_uname" placeholder="Username" name="username" required />
      <input type="password" id="reg_pass" placeholder="Password" name="password" required />
      <input type="password" id="reg_conf_pass" placeholder="Confirm password" required />
      <input type="text" id="reg_email" placeholder="Email address" name="email" required />
      <input type="submit" value="Register" onclick="onRegisterSubmitted()"/>
      <p class="message">Already registered? <a href="#">Sign In</a></p>
    </form>
    <form class="login-form" action="login.php" method="post">
      <input type="text" placeholder="username" name="username" required />
      <input type="password" placeholder="password" name="password" required />
      <input type="submit" value="Login" />
      <p class="message">Not registered? <a href="#">Create an account</a></p>
    </form>
  </div>
</div>

<script src="./js/main.js"></script>

</body>
</html>
