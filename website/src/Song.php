<?
require_once("util.DateFormat.php");

class Song {
  private $id,
    $create_time,
    $artist,
    $album,
    $title,
    $length,
    $time_since;

  function __construct($id, $create_time, $artist, $album, $title, $length) {
    $this->id = $id;
    $this->create_time = $create_time;
    $this->artist = $artist;
    $this->album = $album;
    $this->title = $title;
    $this->length = $length;
    $this->time_since = DateFormat::timeSince($this->create_time);
  }

  // Not used
  public function to_html() {
    $song = '<div class="song">';
    $song .= '<span class="id">' . $this->id . '</span>';
    $song .= '<span class="create_time">' . $this->create_time . '</span>';
    $song .= '<span class="artist">' . $this->artist . '</span>';
    $song .= '<span class="album">' . $this->album . '</span>';
    $song .= '<span class="title">' . $this->title . '</span>';
    $song .= '<span class="length">' . $this->length . '</span>';
    $song .= '</div>';
    return $song;
  }

  public function get_id() {
    return $this->id;
  }

  public function get_create_time() {
    return $this->create_time;
  }

  public function get_since() {
    return $this->time_since;
  }

  public function get_artist() {
    return $this->artist;
  }

  public function get_album() {
    return $this->album;
  }

  public function get_title() {
    return $this->title;
  }

  public function get_length() {
    return $this->length;
  }
}
?>
