<?php
/*
 * User registration page
 */

require_once(__DIR__ . '/config/config.php');
require_once("src/model.User.php");
require_once("src/Template.php");

Template::build_header("Registration");

if (isset($_POST['user']) && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['password_confirm']) && isset($_POST['register'])) {
  if ($_POST['password'] != $_POST['password_confirm']) {
    print("Passwords do not match!");
  } else {
    $user = new User();
    if ($user->register($_POST['user'], $_POST['email'], $_POST['password'])) {
      print("Registration successful.");
    } else {
      print("Registration failed. Username or e-mail already in use!");
    }
  }

// POST not received, show form
} else {
?>
<h1>Registration</h1>
<form action="register.php" method="post">
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
}
Template::build_footer();
