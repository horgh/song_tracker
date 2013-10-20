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
require_once("src/Graphs.php");

/*
 * @param array $graph_array An array from Graphs
 * @param string $class CSS class to use for the table
 * @param string $title Table title
 *
 * @return void
 *
 * SIDE EFFECT: Prints to stdout
 *
 * TODO: convert this to not be a table perhaps
 * TODO: we will not need the first parameter once the conversion to
 *   ajax requests is complete.
 */
function renderTopTable(array $graph_array, $class, $title, $id = NULL) {
  print '<table class="' . htmlspecialchars($class) . '"';
  if ($id !== NULL) {
    print ' id="' . htmlspecialchars($id) . '"';
  }
  print '>';
  print '<th>' . htmlspecialchars($title) . '</th>';
  print '<th>Plays</th>';

  foreach ($graph_array as $item) {
    print "<tr>";
      print '<td class="label">' . htmlspecialchars($item['label']) . '</td>';
      print '<td class="label">' . htmlspecialchars($item['count']) . '</td>';
    print "</tr>";
  }
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
  $graphs = new Graphs($user->id, 10);

  print '<br/>';
  renderTopTable(array(), 'table_left', 'Top Artists (past day)',
    'top_artists_day');
  renderTopTable($graphs->top_songs_day, 'table_right', 'Top Songs (past day)');

  print '<br/>';
  renderTopTable(array(), 'table_left', 'Top Artists (past week)',
    'top_artists_week');
  renderTopTable($graphs->top_songs_week, 'table_right', 'Top Songs (past week)');

  print '<br/>';
  renderTopTable(array(), 'table_left', 'Top Artists (past month)',
    'top_artists_month');
  renderTopTable($graphs->top_songs_1_month, 'table_right', 'Top Songs (past month)');

  print '<br/>';
  renderTopTable(array(), 'table_left', 'Top Artists (past 3 months)',
    'top_artists_three_months');
  renderTopTable($graphs->top_songs_3_months, 'table_right', 'Top Songs (past 3 months)');

  print '<br/>';
  renderTopTable(array(), 'table_left', 'Top Artists (past 6 months)',
    'top_artists_six_months');
  renderTopTable($graphs->top_songs_6_months, 'table_right', 'Top Songs (past 6 months)');

  print '<br/>';
  renderTopTable(array(), 'table_left', 'Top Artists (past year)',
    'top_artists_year');
  renderTopTable($graphs->top_songs_year, 'table_right', 'Top Songs (past year)');

  print '<br/>';
  renderTopTable(array(), 'table_left', 'Top Artists (all time)',
    'top_artists_all_time');
  renderTopTable($graphs->top_songs_all_time, 'table_right', 'Top Songs (all time)');

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
