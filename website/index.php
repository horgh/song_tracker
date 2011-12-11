<?
/*
 * 20/08/2010
 * by horgh
 *
 * Front-end to song/plays database
 */

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 'On');

require_once("src/Template.php");
require_once("src/model.Song.php");
require_once("src/model.User.php");
require_once("src/Graphs.php");

header('Content-type: text/html; charset=utf-8');

if (isset($_GET['user'])) {
  $user = new User();
  if ($user->query_by_name($_GET['user'])) {
    Template::build_header($user->name . "'s music");
    print("<h1>" . $user->name . "'s music</h1>\n");
    print("<h3>Total plays: " . $user->get_play_count() . "</h3>\n");
?>

<table id="just_played">
<th>Artist</th>
<th>Album</th>
<th>Title</th>
<th>Played</th>
<?
    $songs = $user->get_latest_songs(20);
    foreach ($songs as $song) {
      print("<tr>");
      print("<td>" . $song->artist . "</td>");
      print("<td>" . $song->album . "</td>");
      print("<td>" . $song->title . "</td>");
      print("<td>" . $song->play->time_since . "</td>");
      print("</tr>\n");
    }
?>
</table>
<br>
<table id="top_artists">
<th>Top Artists</th>
<th>Plays</th>
<?
  $graphs = new Graphs($user->id, 10);
  foreach ($graphs->get_artists() as $artist) {
    print("<tr>");
    print("<td class=\"label\">" . $artist["label"] . "</td>");
    print("<td class=\"count\">" . $artist["count"] . "</td>");
    print("</tr>\n");
  }
?>
</table>
<table id="top_songs">
<th>Top Songs</th>
<th>Plays</th>
<?
  foreach ($graphs->get_songs() as $topsong) {
    print("<tr>");
    print("<td class=\"label\">" . $topsong["label"] . "</td>");
    print("<td class=\"count\">" . $topsong["count"] . "</td>");
    print("</tr>\n");
  }
?>
</table>
<?
  // Invalid user given
  } else {
    Template::build_header("Invalid user");
    print("User not found.");
  }
// No user set
} else {
  Template::build_header("Welcome");
  print("Welcome to the song tracker.");
?>
<table>
<th>Username</th>
<?
  $users = User::get_all();
  foreach ($users as $user) {
    print("<tr>");
    print("<td><a href=\"index.php?user=" . $user->name . "\">" . $user->name . "</a></td>");
    print("</tr");
  }
}
?>
</table>
<?
Template::build_footer();
?>
