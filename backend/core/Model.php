<?php
abstract class Model {
  protected string $table;
  protected string $pk = 'id';

  public function find($id) {
    $sql = "SELECT * FROM {$this->table} WHERE {$this->pk} = ? LIMIT 1";
    $st = Database::conn()->prepare($sql);
    $st->execute([$id]);
    return $st->fetch();
  }

  public function all($orderBy = 'id DESC') {
    $sql = "SELECT * FROM {$this->table} ORDER BY {$orderBy}";
    return Database::conn()->query($sql)->fetchAll();
  }
}
