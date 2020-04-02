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
      - anotherClassController.php
      
  - views
    - namespace
      - classFunction.php
      - anotherClassFunction.php

  - public
    - index.php
    

the route inside app will be
/namespace/class/function

cast a uppercase first leeter 
/namespace/another-class/function

#helpers functions
    
      - Json( @array ) : string
      - View()
      - first() 
      
      
#js bundle
      
      use Compiler\Minify;

      $vendorfile = "public/core/vendor.js";

      $rootdir = dirname(__DIR__);
      require("$rootdir/vendor/autoload.php");

     $libs = new Minify\JS();

     $libs->add( $ngRoot . "/static/libs/underscore-min.js");
     $libs->cdn("https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js");
   
     //create the file in public
     $libs->build($vendorfile);


