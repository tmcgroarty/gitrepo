<?php

/*
php -S localhost:8000
*/

class Dog {
    public $name;
    public function speak() {
        echo "Rob you were there";
    }
    public function greeting() {
        echo "Hello my name is $this->name";
    }
    public $toys = ["bone", "ball", "rope"];
    public function addtoy($toy_item) {
        $this->toys[] = $toy_item;
    }
}

$dog = new Dog();
$dog->name = "Billy";

//echo $dog->name;
//echo $dog->speak();
//echo $dog->greeting();

//print_r($dog->toys);
$dog->addtoy("scarf");
print_r($dog->toys);

$dog->toys[] = "rug";
//echo $dog->toys[2];
//var_dump(($dog->toys[2]));

foreach ($dog->toys as $toy) {
    echo $toy . "<br>";
}
