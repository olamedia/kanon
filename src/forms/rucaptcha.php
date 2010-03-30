<?php
class randomRuWord {
    var $vowels = array(
	'а','е','и','о','у','э','ю','я',
	'а','е','и','о','у','э','ю','я',
	'а','е','и','о','у','э','ю','я',
	'ую','аю','ию','ою','ая','ея','оя','уя'
	);
    var $consonants = array(
	'б','в','г','д','ж','з','к','л','м','н','п','р','с','т','ф','х','ц','ч','ш',
	'б','в','г','д','ж','з','к','л','м','н','п','р','с','т','ф','х','ц','ч','ш',
	'б','в','г','д','ж','з','к','л','м','н','п','р','с','т','ф','х','ц','ч','ш',
	'бд','бж','бз','бл','бр',
	'вд','вж','вз','вк','вл','вм','вн','вп','вр','вс','вст','гв','гд','гл','гн','гр','дв','дж','дл','дн','др','жл',
	'жм','жн','жр','зв','зд','зл','зм','зн','зр','кв','кл','км','кн','кр','кс','кт','лг','лд','лж','лк','лн','мг','мд','мл','мн',
	'мр','мт','мф','мх','мц','мч','мш','нд','нр','пз','пл','пн','пр','пп','рв','рд','рж','рт','сб','св','ск','сл','см','сн','сп','ср','ст',
	'сф','тв','тл','тм','тр','фл','фр','фс','хл','хм','цв','чр','чл','шв','шр',
	'б','в','г','д','ж','з','к','л','м','н','п','р','с','т','ф','х','ц','ч','ш',
	'б','в','г','д','ж','з','к','л','м','н','п','р','с','т','ф','х','ц','ч','ш',
	'б','в','г','д','ж','з','к','л','м','н','п','р','с','т','ф','х','ц','ч','ш',
	'бд','бж','бз','бл','бр',
	'вд','вж','вз','вк','вл','вм','вн','вп','вр','вс','вст','гв','гд','гл','гн','гр','дв','дж','дл','дн','др','жл',
	'жм','жн','жр','зв','зд','зл','зм','зн','зр','кв','кл','км','кн','кр','кс','кт','лг','лд','лж','лк','лн','мг','мд','мл','мн',
	'мр','мт','мф','мх','мц','мч','мш','нд','нр','пз','пл','пн','пр','пп','рв','рд','рж','рт','сб','св','ск','сл','см','сн','сп','ср','ст',
	'сф','тв','тл','тм','тр','фл','фр','фс','хл','хм','цв','чр','чл','шв','шр',
	);
    var $word = '';
    public function __construct($length = 5, $lower_case = true, $ucfirst = false, $upper_case = false)
    {
        $done = false;
		srand(microtime()*10000);
        $const_or_vowel = rand(1,2);
		srand(microtime()*10000);
        while (!$done)
        {
            switch ($const_or_vowel)
            {
                case 1:
                    $this->word .= $this->consonants[array_rand($this->consonants)];
                    $const_or_vowel = 2;
                    break;
                case 2:
                    $this->word .= $this->vowels[array_rand($this->vowels)];
                    $const_or_vowel = 1;
                    break;
            }

            if (mb_strlen($this->word, 'UTF-8') >= $length)
            {
                $done = true;
            }
        }

        $this->word = mb_substr($this->word, 0, $length, 'UTF-8');
        return $this->word;
    }
}

class ruCaptcha {
	
	public function __construct($width='120',$height='40',$characters='3',$font) {
		global $font,$font_fallback;
		
		//require_once realpath(dirname(__FILE__)).'/lib/randword.class.php';
		$code = new randomRuWord($characters);
		
		/* font size will be 75% of the image height */
		$font_size = $height * 0.85;
		$font_size = 20;
		$image = @imagecreate($width, $height) or die('Cannot initialize new GD image stream');
		/* set the colours */
		$background_color = imagecolorallocate($image, 255, 255, 255);
		$text_color = imagecolorallocate($image, 255, 36, 36);
		$noise_color = imagecolorallocate($image, 255, 36, 36);
		$ttf_supported = true;
		/* generate random lines in background */
		if ($ttf_supported) {
			for( $i=0; $i<($width*$height)/1000; $i++ ) {
				imageline($image, mt_rand(0,$width), mt_rand(0,$height), mt_rand(0,$width), mt_rand(0,$height), $noise_color);
			}
		}
		$font = realpath(dirname(__FILE__)).'/lib/fonts/trebucit.ttf';
			$textbox = imagettfbbox($font_size, 0, $font, $code);
			$x = ($width - $textbox[4])/2;
			$y = ($height - $textbox[5])/2;
			$y -= 5;
			imagettftext($image, $font_size, 0, $x, $y, $text_color, $font, ($code));
		/* create textbox and add text
		$textbox = imagettfbbox($font_size, 0, $font, $code) or die('Error in imagettfbbox function');
		$x = ($width - $textbox[4])/2;
		$y = ($height - $textbox[5])/2;
		imagettftext($image, $font_size, 0, $x, $y, $text_color, $font, $code) or die('Error in imagettftext function');
		output captcha image to browser */
		header('Content-Type: image/jpeg');
		imagejpeg($image);
		imagedestroy($image);
		$_SESSION['security_code'] = $code;
	}
}

$width = 100; // 280
$height = 30;
srand(microtime()*10000);
$characters = rand(3,5);

//$font = realpath(dirname(__FILE__)).'/lib/fonts/monofont.ttf';
$font = realpath(dirname(__FILE__)).'/lib/fonts/trebucit.ttf';
$font = realpath(dirname(__FILE__)).'/lib/fonts/timesbd.ttf';
//$font = realpath(dirname(__FILE__)).'/lib/fonts/lsansdi.ttf';

//$font_fallback = imageloadfont(realpath(dirname(__FILE__)).'/lib/fonts/captchafont.gdf');

$captcha = new ruCaptcha($width,$height,$characters,$font);

?>