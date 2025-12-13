<?php

/*
php -S localhost:8000
*/

class Dog {
    public $name;
}

/*$dog = new Dog()

/*
class FakeDB {
  private $items = [];

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

// ---- TEST IT ----
$db = new FakeDB();

echo "<h2>Before add()</h2>";
echo "<pre>";
print_r($db->all());
echo "</pre>";

$db->add("Pencil", 3);
$db->add("Eraser", 1);

echo "<h2>After add()</h2>";
echo "<pre>";
print_r($db->all());
echo "</pre>";
*/