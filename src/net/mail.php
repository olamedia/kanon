<?php
// Simple mail function with MIME headers and base64 encoded subject
function kanonMail($to, $topic, $message, $headers, $charset = 'UTF-8'){
	$subject = '=?'.$charset.'?B?'.base64_encode($topic).'?=';
	return mail($to, $subject, chunk_split(base64_encode($message)), "MIME-Version: 1.0\r\nContent-Transfer-Encoding: BASE64\r\n".$headers);
}
function isEmail($s){
	$countries = 'ad|ae|af|ag|ai|al|am|an|ao|aq|ar|as|at|au|aw|ax|az|ba|bb|bd|be|bf|bg|bh|bi|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|cr|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|ee|eg|er|es|et|eu|fi|fj|fk|fm|.fo|fr|ga|gb|gd|ge|gf|gg|gh|gi|gl|gm|gn|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|.il|im|in|io|iq|ir|is|it|je|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|me|mg|mh|mk|ml|mm|mn|mo|mp|mq|mr|ms|mt|mu|mv|mw|mx|my|mz|na|nc|ne|nf|ng|ni|nl|no|np|nr|nu|nz|om|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|ps|pt|pw|py|qa|re|ro|rs|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tl|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw';
	$special = 'aero|asia|biz|cat|com|coop|edu|gov|info|int|jobs|mil|mobi|museum|name|net|org|pro|tel|travel|ac';
	/*
	$unameFirst = '[a-z0-9!#$%&\'*+/=?^_`{|}~-]+';
	$uname = $unameFirst.'(?:\.[a-z0-9!#$%&\'*+/=?^_`{|}~-]+)*';
	$subdomain = '(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+';
	$domain = $subdomain.'('.$special.'|'.$countries.')';
	$regexp = '@^'.$uname.'\@'.$domain.'\b$@ims';
	if (preg_match($regexp, $s, $subs)){
		return true;
	}
	return false;*/
	if (preg_match("#^([a-z0-9\.\-\_]+@([a-z0-9-]+\.)+('.$countries.'|'.$special.'))$#ims", $s, $subs)){
		return true;
	}
	return false;
}