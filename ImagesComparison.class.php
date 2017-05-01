<?php
//ImagesComparison Class 0.1 by ming0070913
Class ImagesComparison{
	public $im_a, $im_b, $width, $height, $diff, $indexed, $consistency;

	public function __construct($src_a, $src_b){
		$this->im_a = imagecreatefrompng($src_a);
		$this->im_b = imagecreatefrompng($src_b);
		
		$info_a = getImageSize($src_a);
		$info_b = getImageSize($src_b);

		if($info_a[0] != $info_b[0] || $info_a[1] != $info_b[1])
			return false;
		
		$this->width = $info_a[0];
		$this->height = $info_a[1];
	}

	public function compare(){
		if(!$this->diff){
			for($x=0;$x<$this->width;$x++)//<=  ?
				for($y=0;$y<$this->height;$y++){
					if(imagecolorsforindex($this->im_a, imagecolorat($this->im_a, $x, $y)) !=
						imagecolorsforindex($this->im_b, imagecolorat($this->im_b, $x, $y)))
						$this->diff[$x][$y] = -1;
				}
		}
		return $this->diff;
	}

	public function index(){
		if(!$this->diff) $this->compare();
		if(!$this->indexed){
			$x = $y = $index = 0;
			while($x<$this->width){
				while($y<$this->height){
					if ($this->diff == NULL)
					{
						++$y;
						continue;
					}
					if (!array_key_exists($x, $this->diff))
					{
						++$y;
						continue;
					}
					if (!array_key_exists($y, $this->diff[$x]))
					{
						++$y;
						continue;
					}
					if($this->diff[$x][$y] == -1){
						$this->checkPixel($x, $y, $index);
						$index++;
					}
					$y++;
				}
				$y = 0;
				$x++;
			}
			$this->indexed = true;
		}
		return $this->diff;
		// echo "<pre>";var_dump($this->diff);exit;
	}

	protected function checkPixel($x, $y, $index){
		$this->diff[$x][$y] = $index;
		if (array_key_exists($x-1, $this->diff))
		if (array_key_exists($y-1, $this->diff[$x-1]))
		if($this->diff[$x-1][$y-1] == -1) $this->checkPixel($x-1, $y-1, $index);

		if (array_key_exists($x-1, $this->diff))
		if (array_key_exists($y, $this->diff[$x-1]))
		if($this->diff[$x-1][$y] == -1) $this->checkPixel($x-1, $y, $index);

		if (array_key_exists($x-1, $this->diff))
		if (array_key_exists($y+1, $this->diff[$x-1]))
		if($this->diff[$x-1][$y+1] == -1) $this->checkPixel($x-1, $y+1, $index);

		if (array_key_exists($x, $this->diff))
		if (array_key_exists($y-1, $this->diff[$x]))
		if($this->diff[$x][$y-1] == -1) $this->checkPixel($x, $y-1, $index);
		
		if (array_key_exists($x, $this->diff))
		if (array_key_exists($y+1, $this->diff[$x]))
		if($this->diff[$x][$y+1] == -1) $this->checkPixel($x, $y+1, $index);

		if (array_key_exists($x+1, $this->diff))
		if (array_key_exists($y-1, $this->diff[$x+1]))
		if($this->diff[$x+1][$y-1] == -1) $this->checkPixel($x+1, $y-1, $index);

		if (array_key_exists($x+1, $this->diff))
		if (array_key_exists($y, $this->diff[$x+1]))
		if($this->diff[$x+1][$y] == -1) $this->checkPixel($x+1, $y, $index);

		if (array_key_exists($x+1, $this->diff))
		if (array_key_exists($y+1, $this->diff[$x+1]))
		if($this->diff[$x+1][$y+1] == -1) $this->checkPixel($x+1, $y+1, $index);
	}

	public function consistency(){
		if(!$this->consistency){
			if(!$this->diff) $this->compare();
			foreach($this->diff as $x=>$ys)
				$c += count($ys);
			$this->consistency = 1-$c/($this->width*$this->height);
		}
		return $this->consistency;
	}

	public function fillDiff($rgb = NULL){
		if(!$this->indexed) $this->index();
		if($rgb) $color = imagecolorallocate($this->im_a, $rgb[0], $rgb[1], $rgb[2]);
		foreach($this->diff as $x=>$ys)
			foreach($ys as $y=>$index){
				// echo $index;
				if(!$rgb){
					if(!isset($rand[$index])){
						$rand[$index][0] = rand(0, 255);
						$rand[$index][1] = rand(0, 255);
						$rand[$index][2] = rand(0, 255);
					}
					$color = imagecolorallocate($this->im_a, $rand[$index][0], $rand[$index][1], $rand[$index][2]);
				}
				imageline($this->im_a, $x, $y, $x, $y, $color);
			}
	}

	public function circleDiff($rgb = NULL, $offset = 20){
		if(!$this->indexed) $this->index();
		if($rgb) $color = imagecolorallocate($this->im_a, $rgb[0], $rgb[1], $rgb[2]);
		else $color = imagecolorallocate($this->im_a, 255, 0, 0);
		foreach($this->diff as $x=>$ys)
			foreach($ys as $y=>$index){
				if(!isset($x_[$index])){
					$x_[$index]['max'] = $x;
					$x_[$index]['min'] = $x;
					$y_[$index]['max'] = $y;
					$y_[$index]['min'] = $y;
					continue;
				}
				if($x > $x_[$index]['max']) $x_[$index]['max'] = $x;
				if($x < $x_[$index]['min']) $x_[$index]['min'] = $x;
				if($y > $y_[$index]['max']) $y_[$index]['max'] = $y;
				if($y < $y_[$index]['min']) $y_[$index]['min'] = $y;
			}
		for($i=0;$i<count($x_);$i++){
			if (!array_key_exists($i, $x_))
				continue;
			if (!array_key_exists($i, $y_))
				continue;
			imageellipse($this->im_a, 
				floor(($x_[$i]['max']+$x_[$i]['min'])/2),
				floor(($y_[$i]['max']+$y_[$i]['min'])/2),
				$x_[$i]['max']-$x_[$i]['min']+$offset,
				$y_[$i]['max']-$y_[$i]['min']+$offset,
				$color);
		}
	}
	
	public function printImage($dst = NULL){
		if($dst == NULL) header("Content-type: image/png");
		imagepng($this->im_a, $dst);
	}
}
?>