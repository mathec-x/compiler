# compiler
php-js-css bundle and struct

all routes will respect the order of the controller
just add it on public/index.php


  use Compiler\App\Art;

  $Art = new Art;
  $Art->UseMvc();


on package.json

    "require": {
        "mathec-x/compiler" : "dev-master"
    },

All controls must have the name ending with 'controller', following the order
namespace/class/function

the struct folder


# workingproject

  - controllers
    - namespace
      - classController.php
      - anotherclassController.php
      
  - views
    - namespace
      - classFunction.php
      - anotherclassFunction.php

  - public
    - index.php


   



