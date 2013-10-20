<?php
/*
 * 2010-08-20
 * by horgh
 *
 * Front-end to song/plays database
 */

require_once(__DIR__ . '/config/config.php');
require_once("src/Template.php");
require_once("src/model.Song.php");
require_once("src/model.User.php");

/*
 * @param string $id Id to use for the element
 * @param string $class Class to use for the element
 * @param string $title Table title
 *
 * @return void
 *
 * SIDE EFFECT: Prints to stdout
 *
 * TODO: convert this to not be a table perhaps
 */
function renderTopTable($id, $class, $title) {
  if (!is_string($id) || strlen($id) === 0
    || !is_string($class) || strlen($class) === 0
    || !is_string($title) || strlen($title) === 0) {
    return;
  }
  print '<table class="' . htmlspecialchars($class) . '"
    id="' . htmlspecialchars($id) . '"
    >';
  print '<th>' . htmlspecialchars($title) . '</th>';
  print '<th>Plays</th>';
  print '</table>';
}

/*
 * Begin
 */

if (isset($_GET['user'])) {
  $user = new User();
  if ($user->query_by_name($_GET['user'])) {
    Template::build_header($user->name . "'s music");
    print "<h1>" . htmlspecialchars($user->name) . "'s music</h1>";
    print "<h3>Total plays: " . htmlspecialchars($user->get_play_count())
      . "</h3>";

    // add information on this user to the dom.
    print '
<script>
if (St === undefined) {
  var St = {};
}
St.user_id = ' . $user->id . ';
</script>
';
?>

<table id="just_played">
<th>Artist</th>
<th>Album</th>
<th>Title</th>
<th>Played</th>
<?php
    $songs = $user->get_latest_songs(20);
    foreach ($songs as $song) {
      print "<tr>";
      print "<td>" . htmlspecialchars($song->artist) . "</td>";
      print "<td>" . htmlspecialchars($song->album) . "</td>";
      print "<td>" . htmlspecialchars($song->title) . "</td>";
      print "<td>" . htmlspecialchars($song->play->time_since) . "</td>";
      print "</tr>";
    }
?>
</table>

<br />

<?php
  print '<br/>';
  renderTopTable('top_artists_day', 'table_left', 'Top Artists (past day)');
  renderTopTable('top_songs_day', 'table_right', 'Top Songs (past day)');

  print '<br/>';
  renderTopTable('top_artists_week', 'table_left', 'Top Artists (past week)');
  renderTopTable('top_songs_week', 'table_right', 'Top Songs (past week)');

  print '<br/>';
  renderTopTable('top_artists_month', 'table_left',
    'Top Artists (past month)');
  renderTopTable('top_songs_month', 'table_right', 'Top Songs (past month)');

  print '<br/>';
  renderTopTable('top_artists_three_months', 'table_left',
    'Top Artists (past 3 months)');
  renderTopTable('top_songs_three_months', 'table_right',
    'Top Songs (past 3 months)');

  print '<br/>';
  renderTopTable('top_artists_six_months', 'table_left',
    'Top Artists (past 6 months)');
  renderTopTable('top_songs_six_months', 'table_right',
    'Top Songs (past 6 months)');

  print '<br/>';
  renderTopTable('top_artists_year', 'table_left', 'Top Artists (past year)');
  renderTopTable('top_songs_year', 'table_right', 'Top Songs (past year)');

  print '<br/>';
  renderTopTable('top_artists_all_time', 'table_left',
    'Top Artists (all time)');
  renderTopTable('top_songs_all_time', 'table_right', 'Top Songs (all time)');

  // Invalid user given
  } else {
    Template::build_header("Invalid user");
    print "User not found.";
  }
// No user set
} else {
  Template::build_header("Welcome");
  print "Welcome to the song tracker.";
?>

<table>
<th>Username</th>
<?php
  $users = User::get_all();
  foreach ($users as $user) {
    print "<tr>";
    print "<td>";
    print "<a href=\"index.php?user="
      . htmlspecialchars(rawurlencode($user->name)) . "\">"
      . htmlspecialchars($user->name) . "</a></td>";
    print "</tr>";
  }
}
?>
</table>
<?php
Template::build_footer();
