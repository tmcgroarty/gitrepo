<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] === "GET") {
  echo "This is a GET request";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  echo "This is a POST request";
}

