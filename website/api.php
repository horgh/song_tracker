<?php
/*
 * Provide API functions:
 *  - new play: POST: user, pass, artist, album, title, length
 *  - last play: GET: user, last. 'last' is there simply so we can tell what
 *    request it is.
 *    Optional: format. This may be either plaintext or json. If not provided,
 *    we default to plaintext (for backwards compatibility).
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

  $format = 'plaintext';
  if (array_key_exists('format', $_GET) && is_string($_GET['format']) &&
    $_GET['format'] === 'json') {
    $format = 'json';
  }

  if (!$user->query_by_name($_GET['user'])) {
    print "Invalid user.\n";
    exit;
  }

  $lastSongPlay = $user->get_latest_songs(1);

  if (count($lastSongPlay) === 1) {
    $song = $lastSongPlay[0];

    if ($format === 'plaintext') {
      print $song->artist . " - " . $song->album . " - " . $song->title
            . " (" . $song->length . ")\n";
      exit;
    }

    if ($format === 'json') {
      $raw = array(
        'artist' => $song->artist,
        'album'  => $song->album,
        'title'  => $song->title,
        'length' => $song->length,
      );

      $json = json_encode($raw, JSON_PRETTY_PRINT);
      if (false === $json) {
        print "Unable to encode to JSON\n";
        exit;
      }

      print $json;
      exit;
    }

    print "Unknown response format\n";
    exit;
  }

  print "Error fetching song.\n";
  exit;
}

print "Unknown request.\n";
