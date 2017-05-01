<?php
//ImagesComparison Class Example
require "ImagesComparison.class.php";

//two images with exactly same width and height
$compare = new ImagesComparison("a.png", "b.png");

//compare two images(can skip)
$compare->compare();

//give each part of contiguous difference a number(can skip)
$compare->index();

//check the consistency of two images
//var_dump($compare->consistency());	//(it will send a header, so skip it because of printImage)

//fill in the difference with certain color: array(red, green, blue), else, random color for each contiguous part
$compare->fillDiff();

//circle the difference with certain color: array(red, green, blue), else, red; offset => make the circle bigger
$compare->circleDiff();

//print the image to the browser or a file
$compare->printImage();
?>