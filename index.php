<?php
/*
* Authot: Tony L. Requena (http://www.loreansoft.com/phpmyipcam)
* Version 1.0
*/


// ini_set('memory_limit', '256M');
header('Content-Type: text/html; charset=UTF-8');



$Memory_inicio = memory_get_usage();
class imagediff{
    
    private $image1, $image2;
    private $rectX, $rectY;
    private $diference = array();
    private $different_pixels = array();
    private $debug;
    private $image1src;
    private $image2src;
    private $rectZone;

    function __construct($img1, $img2, $rectX = 50, $rectY = 50, $debug = false)
    {
        $this->image1['path'] = realpath($img1);
        $this->image2['path'] = realpath($img2);
        $this->image1src = $img1;
        $this->image2src = $img2;
        $this->rectX = $rectX;
        $this->rectY = $rectY;
        $this->debug = $debug;
        
        if ($this->image1['path'] === false || $this->image2['path'] === false) {
            throw new Exception('Image "' . htmlspecialchars($this->image1 ? $img2 : $img1) . '" not found!<br />');
        } else {
            $this->image1['type'] = $this->imagetyte($this->image1['path']);
            $this->image2['type'] = $this->imagetyte($this->image2['path']);
        }
    }

    private function imagetyte($imgname)
    {
        $file_info = pathinfo($imgname);
        if (!empty($file_info['extension'])) {
            $filetype = strtolower($file_info['extension']);
            $filetype = $filetype == 'jpg' ? 'jpeg' : $filetype;
            $func = 'imagecreatefrom' . $filetype;
            if (function_exists($func)) {
                return $filetype;
            } else {
                throw new Exception('File type "' . htmlspecialchars($filetype) . '" not supported!<br />');
            }
        } else {
            throw new Exception('File type not supported! <br />');
        }
    }
    public function createDiffImage($i1, $i2){
        /* http://www.phpied.com/image-diff/ */
        
        $this->rectZone[0] = $this->rectX;
        $this->rectZone[1] = $this->rectY;
        
        list($src_W, $src_H) = getimagesize($i1);
        $ratio_src = $src_W/$src_H;
        
        ($this->rectZone[0]/$this->rectZone[1] > $ratio_src) ? $this->rectZone[0] = $this->rectZone[1]*$ratio_src : $this->rectZone[1] = $this->rectZone[0]/$ratio_src;
        
        $i1 = @imagecreatefromjpeg($i1);
        $i2 = @imagecreatefromjpeg($i2);
        
        $zone1 = imagecreate($this->rectZone[0], $this->rectZone[1]);
        imagecopyresampled($zone1, $i1, 0, 0, 0, 0, $this->rectZone[0], $this->rectZone[1], $src_W, $src_H);
        $zone2 = imagecreate($this->rectZone[0], $this->rectZone[1]);
        imagecopyresampled($zone2, $i2, 0, 0, 0, 0, $this->rectZone[0], $this->rectZone[1], $src_W, $src_H);
        
        imagefilter($zone1,IMG_FILTER_GRAYSCALE);
        imagefilter($zone2,IMG_FILTER_GRAYSCALE);
        /*
        $gaussian = array(array(1.0, 2.0, 1.0), array(2.0, 4.0, 2.0), array(1.0, 2.0, 1.0));
        imageconvolution($zone1, $gaussian, 16, 0);
        imageconvolution($zone2, $gaussian, 16, 0);
        imagefilter($zone1,IMG_FILTER_PIXELATE,round($this->rectZone[0]/5,0));
        imagefilter($zone2,IMG_FILTER_PIXELATE,round($this->rectZone[0]/5,0));
        */
        
        
        
        $sx1 = imagesx($zone1);
        $sy1 = imagesy($zone1);
        $diffi = imagecreatetruecolor($sx1, $sy1);
        $red = imagecolorallocate($diffi, 255, 0, 0);
        imagefill($diffi, 0, 0, imagecolorallocate($diffi, 0, 0, 0));
        $different_pixels = 0;
         
        $sumtotal = 0;
        
        for ($x = 0; $x < $sx1; $x++) {
            for ($y = 0; $y < $sy1; $y++) {
         
                $rgb1 = imagecolorat($zone1, $x, $y);
                $pix1 = imagecolorsforindex($zone1, $rgb1);
         
                $rgb2 = imagecolorat($zone2, $x, $y);
                $pix2 = imagecolorsforindex($zone2, $rgb2);
                
                $mediapixels = pow($pix1['red']-$pix2['red'],2) + pow($pix1['green']-$pix2['green'],2) + pow($pix1['blue']-$pix2['blue'],2);
                $sumtotal = $sumtotal + $mediapixels;
                if($mediapixels>1500){
                    $different_pixels++;
                    imagesetpixel($diffi, $x, $y, $red);
                }
            }
        }
        
        if($this->debug==true){
            ob_start();
            imagejpeg($diffi);
            $outputBuffer = ob_get_clean();
            $base64 = base64_encode($outputBuffer);
            $this->diferencePNG = $base64;  
            imagedestroy($diffi);
        }
        
        
        $total = $sx1 * $sy1;
        $percent = number_format(100 * $different_pixels / $total, 2);
        
        array_push($this->different_pixels, $different_pixels, $percent, ($this->rectZone[0]*$this->rectZone[1]));
        return $this->different_pixels;
    }
    private function imagehex($image)
    {
        error_reporting(0);
        ini_set("gd.jpeg_ignore_warning", true);
        $this->rectZone[0] = $this->rectX;
        $this->rectZone[1] = $this->rectY;
        $size = getimagesize($image['path']);
        $func = 'imagecreatefrom' . $image['type'];
        $imageres = $func($image['path']);
        $zone = imagecreate($this->rectZone[0], $this->rectZone[1]);
        imagecopyresized($zone, $imageres, 0, 0, 0, 0, $this->rectZone[0], $this->rectZone[1], $size[0], $size[1]);
        $colormap = array();
        $average = 0;
        $result = array();
        
        for ($x = 0; $x < $this->rectZone[0]; $x++) {
            for ($y = 0; $y < $this->rectZone[1]; $y++) {
                $color = imagecolorat($zone, $x, $y);
                $color = imagecolorsforindex($zone, $color);
                $colormap[$x][$y] = 0.212671 * $color['red'] + 0.715160 * $color['green'] + 0.072169 * $color['blue'];
                $average += $colormap[$x][$y];
            }
        }

        $average /= ($this->rectZone[0] * $this->rectZone[1]);
        for ($x = 0; $x < $this->rectZone[0]; $x++) {
            for ($y = 0; $y < $this->rectZone[1]; $y++) {
                $result[] = ($x < 10 ? $x : chr($x + 97)) . ($y < 10 ? $y : chr($y + 97)) . round(2 * $colormap[$x][$y] / $average);
            }
        }
        
        if($this->debug==true){
            ob_start();
            imagejpeg($zone);
            $outputBuffer = ob_get_clean();
            $base64 = base64_encode($outputBuffer);
            array_push($this->diference, $base64);
            imagedestroy($zone);    
        }
        
        return $result;
    }

    public function diff()
    {
        $hex1 = $this->imagehex($this->image1);
        $hex2 = $this->imagehex($this->image2);
        $result = (count($hex1) + count($hex2)) - count(array_diff($hex2, $hex1)) - ($this->rectZone[0] * $this->rectZone[1]);
        return $result / ((count($hex1) + count($hex2)) / 2);
    }
    public function imgResult()
    {
        echo "<img width=200 src='" . $this->image1src . "'>";
        echo "<img width=200 src='" . $this->image2src . "'> = ";
        echo '<img src="data:image/jpeg;base64,' . $this->diference[0] . '" alt="photo">';
        echo '<img src="data:image/jpeg;base64,' . $this->diference[1]. '" alt="photo"> ';
        
        // if ($this->createDiffImage($this->image1src, $this->image2src)){
        //     echo '<img width=400 src="data:image/jpeg;base64,' . $this->diferencePNG . '"> <span>'.$this->different_pixels[1].'% difference</span><br />';
        // }
    }
    public function getDebug(){
        return $this->debug;
    }
    
    
}

$Time_inicio_total = microtime(true);

$image01 = 'print1.png';
$image02 = 'print2.png';
$tempImage1 = getImageSize($image01);
$width = $tempImage1[0];
$height = $tempImage1[1];

$Time_inicio = microtime(true);
$diff = new imagediff($image01, $image02, $width/4, $height/4, true);
echo "Size: " . $width/4 . " X " . $height/4 . "<br />";
print 'Equality: ' . ($diff->diff() * 100) . '%<br />';
if ($diff->getDebug() == true) {
    $diff->imgResult();
    $Time_fin = microtime(true);
    $Memory_fin = memory_get_usage();
}


$Time_inicio = microtime(true);
$diff2 = new imagediff($image01, $image02, $width/3, $height/3, true);
echo "<br>Size: " . $width/3 . " X " . $height/3 . "<br />";
print 'Equality: ' . ($diff2->diff() * 100) . '%<br />';
if ($diff2->getDebug() == true) {
    $diff2->imgResult();
    $Time_fin = microtime(true);
    $Memory_fin = memory_get_usage();
}


$Time_inicio = microtime(true);
$diff3 = new imagediff($image01, $image02, $width/2, $height/2, true);
echo "<br>Size: " . $width/2 . " X " . $height/2 . "<br />";
print 'Equality: ' . ($diff3->diff() * 100) . '%<br />';
if ($diff3->getDebug() == true) {
    $diff3->imgResult();
    $Time_fin = microtime(true);
    $Memory_fin = memory_get_usage();
}

if ($diff->getDebug() == true) {
    $Time_fin_total = microtime(true);
    $Memory_fin = memory_get_usage();
    $ratio1 = ( ( ($width/4) + ($height/4) ) / ( $width + $height ) * ( $diff->diff() * 100));
    $ratio2 = ( ( ($width/3) + ($height/3) ) / ( $width + $height ) * ($diff2->diff() * 100));
    $ratio3 = ( ( ($width/2) + ($height/2) ) / ( $width + $height ) * ($diff3->diff() * 100));
    echo "<br>Normalized Match Rate: " . ($ratio1 + $ratio2 + $ratio3) . "<br>";
}
