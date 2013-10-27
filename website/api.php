<?php
/*
 * Provide api functions:
 *  - new play: POST: user, pass, artist, album, title, length
 *  - last play: GET: user, last
 *
 * NOTE: response is not html encoded right now - it's just plaintext.
 *
 * TODO:
 * - respond with json instead
 * - require an action parameter to identify the api request rather
 *   than just looking for the required arguments to identify it.
 */

require_once(__DIR__ . '/config/config.php');

require_once(__DIR__ . '/src/model.User.php');
require_once(__DIR__ . '/src/API.php');

header('Content-type: text/plain; charset=utf-8');

// a play was sent.
if (isset($_POST['artist']) && isset($_POST['album'])
  && isset($_POST['title']) && isset($_POST['length'])
  && isset($_POST['pass']) && isset($_POST['user']))
{

  if (!API::add_user_play($_POST['user'], $_POST['pass'], $_POST['artist'],
    $_POST['album'], $_POST['title'], $_POST['length']))
  {
    print "Error recording the play.\n";
    print "api.php: Artist: " . $_POST['artist']
      . " album: " . $_POST['album'] . " title: " . $_POST['title']
      . " length " . $_POST['length'] . "\n";
    exit;
  }
  print "Play recorded.\n";
  exit;
}

// Last song played in plain text for script (for now playing)
if (isset($_GET['last']) && isset($_GET['user'])) {
  $user = new User();
  if ($user->query_by_name($_GET['user'])) {
    $lastSongPlay = $user->get_latest_songs(1);
    if (count($lastSongPlay) === 1) {
      $song = $lastSongPlay[0];
      print $song->artist . " - " . $song->album . " - " . $song->title
            . " (" . $song->length . ")\n";
      exit;
    }
    print "Error fetching song.\n";
    exit;
  }
  print "Invalid user.\n";
  exit;
}

// unknown api request.
print "Unknown request.\n";
