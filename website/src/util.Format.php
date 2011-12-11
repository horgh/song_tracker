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
   * @return string Length in form: milliseconds. -1 if error
   *
   * Length given in form hh:mm:ss or milliseconds, return in form of seconds
   * (hh: is optional)
   *
   * Used by add_play()
   */
  public static function fix_length($length) {
    // Already in ms (we assume)
    if (is_numeric($length)) {
      return $length;
    }

    // If ":" found, assume form: (hh:)?mm:ss
    if (strpos($length, ":") !== false) {
      if (preg_match('/^(\d+)?:?(\d+):(\d+)$/', $length, $matches) === false) {
        return -1;
      }
      // even if hh did not match, [1] still gets set (to 0)
      $seconds = $matches[1] * 60 * 60 + $matches[2] * 60 + $matches[3];
      $ms = $seconds * 1000;
      return $ms;
    }
    return $length;
  }
}
?>
