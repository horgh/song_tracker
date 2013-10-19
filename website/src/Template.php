<?php
/*
 * Provide some templating / generic html
*/

class Template {
  public static function build_header($title) {
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="style.css" rel="stylesheet" type="text/css" />
<?php
print "<title>" . htmlspecialchars($title) . "</title>";
?>
</head>
<body>
<?php
  }

  public static function build_footer() {
?>
</body>
</html>
<?php
  }
}
