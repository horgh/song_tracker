<?php
/*
 * User registration page
 */

require_once(__DIR__ . '/config/config.php');
require_once(__DIR__ . '/src/Template.php');
require_once(__DIR__ . '/src/API.php');

Template::build_header('Registration');

if (isset($_POST['user']) && isset($_POST['email'])
	&& isset($_POST['password']) && isset($_POST['password_confirm'])
  && isset($_POST['register']))
{
  $pass1 = trim($_POST['password']);
  $pass2 = trim($_POST['password_confirm']);
  $username = strtolower(trim($_POST['user']));
  $email = strtolower(trim($_POST['email']));

  if ($pass1 !== $pass2) {
    print "Passwords do not match!";
    exit;
  }

  if (API::add_user($username, $email, $pass1)) {
    print "Registration successful.";
  } else {
    print "Registration failed. Username or e-mail already in use!";
  }

  exit;
}

?>
<h1>Registration</h1>
<form action="register.php" method="POST"
  autocomplete="off">
  <table>
    <tr>
      <th>Username</th>
      <td><input type="text" size="10" name="user" maxlength="20" /></td>
    </tr>
    <tr>
      <th>E-mail</th>
      <td><input type="text" size="25" name="email" maxlength="60" /></td>
    </tr>
    <tr>
      <th>Password</th>
      <td><input type="password" size="10" name="password" maxlength="60" /></td>
    </tr>
    <tr>
      <th>Confirm password</th>
      <td><input type="password" size="10" name="password_confirm" maxlength="60" /></td>
    </tr>
    <tr>
      <td><input type="submit" value="Register" name="register" /></td>
    </tr>
  </table>
</form>

<?php
Template::build_footer();
