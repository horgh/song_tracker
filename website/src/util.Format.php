<?
/*
 * Taken from MyQuote, written by me!
 */

class Format {
  /*
   * Take date formatted like 2010-02-07 22:09:18
   * and return x seconds ago, or x hours ago, etc
   */
  public static function timeSince($timeStamp) {
    $unixTimeStamp = strtotime($timeStamp);
    $diff = time() - $unixTimeStamp;

    if ($diff < 60)
      if ($diff == 1)
        return $diff . " second ago";
      else
        return $diff . " seconds ago";

    $diff = round($diff / 60);
    if ($diff < 60)
      if ($diff == 1)
        return $diff . " minute ago";
      else
        return $diff . " minutes ago";

    $diff = round($diff / 60);
    if ($diff < 24)
      if ($diff == 1)
        return $diff . " hour ago";
      else
        return $diff . " hours ago";

    $diff = round($diff / 24);
    if ($diff < 7)
      if ($diff == 1)
        return $diff . " day ago";
      else
        return $diff . " days ago";

    $diff = round($diff / 7);
    if ($diff == 1)
      return $diff . " week ago";
    else
      return $diff . " weeks ago";
  }

  /*
   * @return string Length in form mm:ss
   *
   * Length given in form mm:ss or milliseconds, return in form of mm:ss
   *
   * Used by add_play()
   */
  public static function fix_length($length) {
    // If no ":" found, assume time given in milliseconds
    if (strpos($length, ":") === false) {
      $length = $length / 1000;
      $minutes = floor($length / 60);
      $seconds = $length % 60;
      $length = sprintf("%02d:%02d", $minutes, $seconds);
    }
    return $length;
  }
}
?>