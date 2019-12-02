<?php

include 'config.php';

// $array = array('print("Cats " + "and " + "dogs")', 'print("cats " + "and " + "dogs")', 'print("Cats "+"and "+"dogs")', 'print("cats "+"and "+"dogs")');

// echo json_encode($array);

echo $auth->getRandomPassword(2);
