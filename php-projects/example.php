<?php

/*
php -S localhost:8000
*/

//print variables with echo and array and object with print_r
// () = function, [] = array -> = object access

//think define docker file
class Dog {
    public $name;
    public function speak() {
        echo "Rob you were there";
    }

    public $toys = ["bone", "ball", "rope"];

    public function addtoy($toy_item) {
        $this->toys[] = $toy_item;
    }
    public function removetoy($remove) {
        unset($this->toys[$remove]);
    }
}

//think build docker container 
$dog = new Dog();

//think add attribute to docker container
$dog->name = "Billy";

//think test methods in docker container
echo "$dog->name";
print_r($dog->toys);

//if you dont know what something is use var_dump
//var_dump(($dog->toys[2]));

$dog->addtoy("scarf");
print_r($dog->toys);

$dog->removetoy("rug");


/*

foreach ($dog->toys as $toy) {
    echo $toy . "<br>";
}

$searchresult = array_search("ball", $dog->toys);

if ($searchresult !== false) {
        unset($dog->toys[$searchresult]);
}

print_r($dog->toys);
*/