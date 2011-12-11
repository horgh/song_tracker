<?php
/*
 * Functions generic to model classes
 */

require_once("Database.php");
require_once("Logger.php");

class Model {
  /*
   * @param string $str
   *
   * @return string Given string pluralized
   */
  private function pluralize($str) {
    return $str . 's';
  }

  /*
   * @return string Name of the table associated with the model
   */
  private function get_table_name() {
    // get_called_class() gets name of actual object's class.
    // get_class() would always return this class's name even if
    // the object is actually a child class
    $table_name = strtolower(get_called_class());
    $table_name = $this->pluralize($table_name);
    return $table_name;
  }
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
    $table_name = $this->get_table_name();
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
    $table_name = $this->get_table_name();
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

  /*
   * @return bool Whether all fields the model requires are set
   */
  private function fields_set() {
    foreach ($this->fields as $field) {
      // We don't require these to be set
      if ($field === 'create_time' || $field === 'id') {
        continue;
      }

      if (!isset($this->$field) || empty($this->$field)) {
        Logger::log("fields_set: required field not set: $field");
        return false;
      }
    }
    return true;
  }

  /*
   * @return bool Whether successful
   *
   * Update an existing row in the database with the model's data
   */
  private function update() {
    // Build SQL statement
    $sql = "UPDATE " . $this->get_table_name()
         . " SET ";

    $field_names = $this->get_field_names();
    foreach ($field_names as $field) {
      $sql .= " $field = ?";
    }
    $sql .= " WHERE id = ?";

    // Build params array
    $params = $this->get_field_values();
    
    // Execute
    $db = Database::instance();
    try {
      $count = $db->manipulate($sql, $params, 1);
    } catch (Exception $e) {
      Logger::log("update: database failure: " . $e->getMessage());
      return false;
    }
    return $count === 1;
  }

  /*
   * @return int Number of fields that need to be inserted/updated
   */
  private function get_field_count() {
    // -1 for id
    $field_count = count($this->fields) - 1;
    // and create_time (if exists)
    if (in_array('create_time', $this->fields)) {
      $field_count--;
    }
    return $field_count;
  }

  /*
   * @return string: field names separated by comma
   *
   * Form: (field1, field2) etc
   *
   * Necessary as some fields we do not want to use
   * (id, create_time)
   */
  private function get_field_names() {
    $fields = '(';
    foreach ($this->fields as $field) {
      if ($field === 'id' || $field === 'create_time') {
        continue;
      }
      $fields .= "$field,";
    }
    return preg_replace('/,$/', '', $fields) . ')';
  }

  /*
   * @return array of strings: field values
   *
   * Some fields we do not include:
   * - id, create_time
   */
  private function get_field_values() {
    $field_values = array();
    foreach ($this->fields as $field) {
      if ($field === 'id' || $field === 'create_time') {
        continue;
      }
      $field_values[] = $this->$field;
    }
    return $field_values;
  }

  /*
   * @return string of ?, ?, etc. One ? for each field
   *
   * Form: (?, ?)
   *
   * We do not count certain fields:
   * - id, create_time
   */
  private function get_bind_fields() {
    // bind fields for our fields
    $fields_sql = '(';
    for ($i = 0; $i < $this->get_field_count(); $i++) {
      $fields_sql .= '?,';
    }
    $fields_sql = preg_replace('/,$/', '', $fields_sql);
    $fields_sql .= ')';
    return $fields_sql;
  }

  /*
   * @return bool Whether successful
   *
   * Save the model to the database
   *
   * If the id of the model already exists in the database, update the
   * row
   * Otherwise add a new row
   */
  public function store() {
    if (!$this->fields_set()) {
      Logger::log("store: a required field is not set");
      return false;
    }

    // Check whether a row for this id exists
    $modelObj = new self();
    if (isset($this->id) && $modelObj->query_by_id($this->id)) {
      return $this->update();
    }

    // Not yet in database. Insert it
    $db = Database::instance();

    // Build statement
    // Note that we do not bind the table name nor the field names as
    // this seems to not be possible?
    $sql = "INSERT INTO " . $this->get_table_name()
         . ' ' . $this->get_field_names()
         . " VALUES " . $this->get_bind_fields();

    // Build params
    $params = $this->get_field_values();
    print "sql: $sql. params: " . print_r($params, 1);

    try {
      $count = $db->manipulate($sql, $params, 1);
    } catch (Exception $e) {
      Logger::log("store: database failure: " . $e->getMessage());
      return false;
    }
    return $count === 1;
  }
}
?>
