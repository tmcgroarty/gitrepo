<?php
class FakeDB {
 private array $items = [];

  public function __construct() {
    $this->items = [
      ["id"=>1,"name"=>"Pen","qty"=>2],
      ["id"=>2,"name"=>"Book","qty"=>1]
    ];
  }

  public function all() {
    return $this->items;
  }

  public function add($name, $qty) {
    $this->items[] = [
      "id" => count($this->items) + 1,
      "name" => $name,
      "qty" => $qty
    ];
  }
}
