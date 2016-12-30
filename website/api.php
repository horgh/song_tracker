<?php
/*
 * Provide API functions:
 *  - new play: POST: user, pass, artist, album, title, length
 *  - last play: GET: user, last
 *
 * NOTE: Response is plaintext UTF-8. No HTML encoding or anything.
 *
 * TODO:
 * - Respond with JSON
 * - Require an action parameter to identify the API request rather
 *   than just looking for the required arguments to identify it.
 */

require_once(__DIR__ . '/config/config.php');
require_once(__DIR__ . '/src/model.User.php');
require_once(__DIR__ . '/src/API.php');

header('Content-Type: text/plain; charset=utf-8');

// Play sent.
if (isset($_POST['artist']) && isset($_POST['album']) &&
  isset($_POST['title']) && isset($_POST['length']) &&
  isset($_POST['pass']) && isset($_POST['user']))
{

  if (!API::add_user_play($_POST['user'], $_POST['pass'], $_POST['artist'],
    $_POST['album'], $_POST['title'], $_POST['length']))
  {
    print "Error recording the play.\n";
    print "Artist: " . $_POST['artist']
      . " album: " . $_POST['album'] . " title: " . $_POST['title']
      . " length " . $_POST['length'] . "\n";
    exit;
  }

  print "Play recorded.\n";
  exit;
}

// Retrieve last song played.
if (isset($_GET['last']) && isset($_GET['user'])) {
  $user = new User();

  if (!$user->query_by_name($_GET['user'])) {
    print "Invalid user.\n";
    exit;
  }

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

print "Unknown request.\n";
