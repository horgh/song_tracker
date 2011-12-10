<?
/*
 * Provide api functions:
 *  - new play: POST: user, pass, artist, album, title, length
 *  - last play: GET: user, last
 */

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 'On');

require_once("src/Song.php");
require_once("src/User.php");
require_once("src/util.Query.php");

header ('Content-type: text/html; charset=utf-8');

// Update sent
if (isset($_POST['artist'])
  && isset($_POST['album'])
  && isset($_POST['title'])
  && isset($_POST['length'])
  && isset($_POST['pass'])
    && isset($_POST['user'])) {
  $user = new User($_POST['user'], $_POST['pass']);
  if (!$user->is_valid()) {
    print("Invalid username or password.");
  } else {
    /*
    $debug = "a " . $_POST['artist'] . " al " . $_POST['album'] . " ti " . $_POST['title'];
    print($debug . "\n");
    print(urldecode($debug) . "\n");
    */

    if(!Query::add_play($user, $_POST['artist'], $_POST['album'], $_POST['title'], $_POST['length'])) {
      print("Error recording the play.");
      print("\napi.php: Artist: " . $_POST['artist'] . " album: " . $_POST['album'] . " title: " . $_POST['title'] . " length " . $_POST['length']);
    }
    print "play recorded";
  }

// Last song played in plain text for script (for now playing)
} elseif (isset($_GET['last'])
    && isset($_GET['user'])) {
  $user_id = Query::get_id_by_name($_GET['user']);
  if ($user_id != -1) {
    $songs = Query::get_songs($user_id, 1);
    if (count($songs) == 1) {
      $song = $songs[0];
      print($song->get_artist() . " - " . $song->get_album() . " - " . $song->get_title() . " (" . $song->get_length() . ")");
    } else {
      print("Error fetching song.");
    }
  } else {
    print("Invalid user.");
  }
}
?>
