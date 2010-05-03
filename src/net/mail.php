<?php
// Simple mail function with MIME headers and base64 encoded subject
function kanon_mail($to, $topic, $message, $headers, $charset = 'UTF-8'){
	$subject = '=?'.$charset.'?B?'.base64_encode($topic).'?=';
	return mail($to, $subject, chunk_split(base64_encode($message)), "MIME-Version: 1.0\r\nContent-Transfer-Encoding: BASE64\r\n".$headers);
}