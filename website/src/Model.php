<?php
/*
 * Functions generic to model classes
 */

require_once("Database.php");
require_once("Logger.php");

class Model {
  /*
   * @param string $field   Database field to select with (WHERE field)
   * @param string $data    Data to use with the field
   *
   * @return bool Whether successful
   *
   * Fill the object with its data from database
   * A single row should be found with the field data
   */
  public function query_by_field($field, $data) {
    if (strlen($field) === 0 || strlen($data) === 0) {
      Logger::log("query_by_field: invalid field or data given");
      return false;
    }

    $db = Database::instance();
    $table_name = strtolower(get_class());
    $sql = "SELECT * FROM ? WHERE ? = ?";
    $params = array($table_name, $field, $data);
    try {
      $rows = $db->select($sql, $params);
    } catch (Exception $e) {
      Logger::log("query_by_field: failed to retrieve from db: " . $e->getMessage());
      return false;
    }

    if (count($rows) !== 1) {
      Logger::log("query_by_field: no row found with that data");
      return false;
    }
    return $this->fill_fields($rows[0]);
  }

  /*
   * @param int $id
   *
   * @return bool Whether successful
   *
   * Fill object with data from database
   * Use id column
   */
  public function query_by_id($id) {
    if (!is_numeric($id)) {
      Logger::log("query_by_id: invalid id: $id");
      return false;
    }
    return $this->query_by_field("id", $id);
  }

  /*
   * @return array of objects of the model's type
   */
  private function get_all() {
    $db = Database::instance();
    $table_name = strtolower(get_class());
    $sql = "SELECT * FROM ?";
    $params = array($table_name);
    try {
      $rows = $db->select($sql, $params);
    } catch (Exception $e) {
      Logger::log("get_all: retrieval from database failure: " . $e->getMessage());
      return array();
    }
    $objects = array();
    foreach ($rows as $row) {
      $obj = new self();
      if (!$obj->fill_fields($row)) {
        Logger::log("get_all: failure building a model object");
        return array();
      }
      $objects[] = $obj;
    }
    return $objects;
  }

  /*
   * @param array $row   A row from the database which should have all the
   *                     fields we require
   *
   * @return bool Whether successful
   */
  private function fill_fields(array $row) {
    if (!is_array($this->fields)) {
      Logger::log("fill_fields: Error: fields array is not set!");
      return false;
    }

    foreach ($this->fields as $field) {
      if (!array_key_exists($field, $row)) {
        Logger::log("fill_fields: required field not present: $field");
        return false;
      }
      $this->$field = $row[$field];
    }
    return true;
  }
}
?>
