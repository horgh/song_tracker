<?
/*
 * Provide api functions:
 *  - new play: POST: user, pass, artist, album, title, length
 *  - last play: GET: user, last
 */

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 'On');

require_once("src/model.Song.php");
require_once("src/model.User.php");
require_once("src/model.Play.php");

header ('Content-type: text/html; charset=utf-8');

// Update sent
if (isset($_POST['artist'])
  && isset($_POST['album'])
  && isset($_POST['title'])
  && isset($_POST['length'])
  && isset($_POST['pass'])
    && isset($_POST['user'])) {
  $user = new User($_POST['user'], $_POST['pass']);
  if (!$user->authenticate($_POST['user'], $_POST['pass'])) {
    print("Invalid username or password.");
  } else {
    /*
    $debug = "a " . $_POST['artist'] . " al " . $_POST['album'] . " ti " . $_POST['title'];
    print($debug . "\n");
    print(urldecode($debug) . "\n");
    */

    if(!Play::add_play($user, $_POST['artist'], $_POST['album'], $_POST['title'], $_POST['length'])) {
      print("Error recording the play.");
      print("\napi.php: Artist: " . $_POST['artist'] . " album: " . $_POST['album'] . " title: " . $_POST['title'] . " length " . $_POST['length']);
    } else {
      print "Play recorded.";
    }
  }

// Last song played in plain text for script (for now playing)
} elseif (isset($_GET['last'])
    && isset($_GET['user']))
{
  $user = new User();
  if ($user->query_by_name($_GET['name'])) {
    $lastSongPlay = $user->get_latest_songs(1);
    if (count($lastSongPlay) == 1) {
      $song = $songs[0];
      print($song->artist . " - " . $song->album . " - " . $song->title
            . " (" . $song->length . ")");
    } else {
      print("Error fetching song.");
    }
  } else {
    print("Invalid user.");
  }
}
?>
