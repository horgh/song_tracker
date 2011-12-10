<?
/*
 * Must set the $DB_ vars below
 */

class Database {
  private static $DB_HOST = "beast";
  private static $DB_USER = "songs";
  private static $DB_PASS = "songs";
  private static $DB_NAME = "songs";

  private static $instance;

  private $dbh;

  /*
   * Throws exception if fails to connect
   */
  function __construct() {
    $dsn = "pgsql:dbname=" . self::$DB_NAME
         . ";host=" . self::$DB_HOST;

    $this->dbh = new PDO($dsn, self::$DB_USER, self::$DB_PASS);
    // exception on error
    $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }

  public static function instance() {
    if (!isset(self::$instance)) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  private function query($sql, array $params) {
    if (($sth = $this->dbh->prepare($sql)) === false) {
      throw new Exception("failed to prepare statement: $sql");
    }

    if (!$sth->execute($params)) {
      throw new Exception("failed to execute statement: $sql");
    }
    return $sth;
  }

  /*
   * @param string $sql
   * @param array $params
   *
   * @return array $rows
   */
  function select($sql, array $params) {
    $sth = $this->query($sql, $params);
    $rows = array();
    while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
      $rows[] = $row;
    }
    return $rows;
  }

  /*
   * @param string $sql
   * @param array $params
   * @param int $expected
   *
   * @return int number of rows affected
   *
   * Manipulation query (e.g. INSERT, DELETE, UPDATE)
   *
   * If $expected is non NULL, must be a numeric value and we will
   * error if this many rows were not affected
   */
  function manipulate($sql, array $params, $expected = NULL) {
    $this->beginTransaction();

    // Since we're in a transaction, wrap this up so we end the
    // transaction correctly
    try {
      $sth = $this->query($sql, $params);
    } catch (Exception $e) {
      $this->rollBack();
      throw new Exception("failure executing query: " . $e->getMessage());
    }

    if ($expected !== NULL) {
      if ($sth->rowCount() != $expected) {
        $this->rollBack();
        throw new Exception("unexpected number of rows affected: got "
          . $sth->rowCount() . " but wanted $expected");
      }
    }
    $this->commit();
    return $sth->rowCount();
  }

  function beginTransaction() {
    if ($this->dbh->beginTransaction() === false) {
      throw new Exception("Failure beginning transaction!");
    }
  }

  function commit() {
    if ($this->dbh->commit() === false) {
      throw new Exception("Failure committing transaction!");
    }
  }

  function rollBack() {
    if ($this->dbh->rollBack() === false) {
      throw new Exception("Failure rolling back transaction!");
    }
  }
}
?>
