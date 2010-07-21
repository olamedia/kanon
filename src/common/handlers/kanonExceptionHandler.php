<?php
/**
 * Exception handler, which displays nice formatted stack trace
 * and some additional useful information.
 *
 * Original idea and code from symphony 2.0 (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license.
 *
 * @author W.Ed. <olamedia@gmail.com>
 * @link   http://olamedia.ru/
 */
class kanonExceptionHandler{
	protected static $_isDeveloperMode = false;
	public static function setDeveloperMode($mode = true){
		self::$_isDeveloperMode = $mode;
	}
	public static function getDeveloperMode(){
		return self::$_isDeveloperMode;
	}
	static function handle(Exception $exception){
		$currentContent = $allParams = '';
		/*if(class_exists('Zend_Controller_Front', false)) {
		 $front    = Zend_Controller_Front::getInstance();
		 $response = $front->getResponse();
		 $request  = $front->getRequest();
		 $currentContent = $response->getBody();
		 $allParams      = array('isDispatched' => $request->isDispatched())
		 + $request->getParams();
		 $allParams = var_export($allParams, true);
		 }*/
		if(empty($currentContent)) {
			while (false !== $content = ob_get_clean()) { $currentContent .= $content; }
		}
		$params = array(
            'code'      => 500,
            'text'      => "Internal Error",
            'message'   => null === $exception->getMessage() ? 'n/a' : $exception->getMessage(),
            'name'      => get_class($exception),
            'traces'    => self::getTraces($exception, 'html'),
            'currentContent' => $currentContent,
            'allParams'      => $allParams,
		);
		return self::render($params);
	}

	/**
	 * Returns an array of exception traces.
	 *
	 * @param Exception $exception  An Exception implementation instance
	 * @param string    $format     The trace format (txt or html)
	 *
	 * @return array An array of traces
	 */
	static function getTraces(Exception $exception, $format = 'txt'){
		$traceData = $exception->getTrace();
		array_unshift($traceData, array(
          'function' => '',
          'file'     => $exception->getFile() != null ? $exception->getFile() : null,
          'line'     => $exception->getLine() != null ? $exception->getLine() : null,
          'args'     => array(),
		));
		$traces = array();
		if ($format == 'html'){
			$lineFormat = 'at <strong>%s%s%s</strong>(%s)<br />in <em>%s</em> line %s <a href="#" onclick="toggle(\'%s\'); return false;">...</a><br /><ul class="code" id="%s" style="display: %s">%s</ul>';
		}else{
			$lineFormat = 'at %s%s%s(%s) in %s line %s';
		}
		for ($i = 0, $count = count($traceData); $i < $count; $i++){
			$line = isset($traceData[$i]['line']) ? $traceData[$i]['line'] : null;
			$file = isset($traceData[$i]['file']) ? $traceData[$i]['file'] : null;
			$args = isset($traceData[$i]['args']) ? $traceData[$i]['args'] : array();
			$traces[] = sprintf($lineFormat,
				(isset($traceData[$i]['class']) ? $traceData[$i]['class'] : ''),
				(isset($traceData[$i]['type']) ? $traceData[$i]['type'] : ''),
				$traceData[$i]['function'],
				self::formatArgs($args, false, $format),
				self::formatFile($file, $line, $format, null === $file ? 'n/a' : $file),
				null === $line ? 'n/a' : $line,
	            'trace_'.$i,
	            'trace_'.$i,
				$i == 0 ? 'block' : 'none',
				self::fileExcerpt($file, $line)
			);
		}
		return $traces;
	}
	/**
	 * Returns an excerpt of a code file around the given line number.
	 *
	 * @param string $file  A file path
	 * @param int    $line  The selected line number
	 *
	 * @return string An HTML string
	 */
	static protected function fileExcerpt($file, $line){
		if (!self::$_isDeveloperMode) return '';
		if (is_readable($file)) {
			$content = preg_split('#<br />#', highlight_file($file, true));
			$lines = array();
			for ($i = max($line - 3, 1), $max = min($line + 3, count($content)); $i <= $max; $i++){
				$lines[] = '<li'.($i == $line ? ' class="selected"' : '').'>'.$content[$i - 1].'</li>';
			}
			return '<ol start="'.max($line - 3, 1).'">'.implode("\n", $lines).'</ol>';
		}
	}
	/**
	 * Formats an array as a string.
	 *
	 * @param array   $args     The argument array
	 * @param boolean $single
	 * @param string  $format   The format string (html or txt)
	 *
	 * @return string
	 */
	static protected function formatArgs($args, $single = false, $format = 'html'){
		$result = array();
		$single and $args = array($args);
		foreach ($args as $key => $value){
			if (is_object($value)){
				$formattedValue = ($format == 'html' ? '<em>object</em>' : 'object').sprintf("('%s')", get_class($value));
			}elseif (is_array($value)){
				$formattedValue = ($format == 'html' ? '<em>array</em>' : 'array').sprintf("(%s)", self::formatArgs($value));
			}elseif (is_string($value)){
				$formattedValue = ($format == 'html' ? sprintf("'%s'", self::escape($value)) : "'$value'");
			}elseif (null === $value){
				$formattedValue = ($format == 'html' ? '<em>null</em>' : 'null');
			}else{
				$formattedValue = $value;
			}
			$result[] = is_int($key) ? $formattedValue : sprintf("'%s' => %s", self::escape($key), $formattedValue);
		}
		return implode(', ', $result);
	}

	/**
	 * Formats a file path.
	 *
	 * @param  string  $file   An absolute file path
	 * @param  integer $line   The line number
	 * @param  string  $format The output format (txt or html)
	 * @param  string  $text   Use this text for the link rather than the file path
	 *
	 * @return string
	 */
	static protected function formatFile($file, $line, $format = 'html', $text = null){
		if (null === $text){
			$text = $file;
		}
		$linkFormat = 'rcp:C:\workspace\next.shop66.ru%f';//?%l?0-0
		if ('html' === $format && $file && $line && $linkFormat){
			$localFilename = str_replace(kanon::getBasePath(), '', $file); 
			$text = $localFilename;
			$link = strtr($linkFormat, array('%f' => $localFilename, '%l' => $line));
			//$link = 'data:link/php;base64,'.base64_encode($localFilename);
			$text = sprintf('<a href="%s" title="Click to open this file" class="file_link">%s</a>', $link, $text);
		}

		return $text;
	}

	/**
	 * Escapes a string value with html entities
	 *
	 * @param  string  $value
	 *
	 * @return string
	 */
	static protected function escape($value, $charset = 'utf-8'){
		if (!is_string($value)){
			return $value;
		}
		return htmlspecialchars($value, ENT_QUOTES, $charset);
	}

	static function render(array $params, $charset = 'utf-8'){
		foreach(array('message') as $name) {
			$tmp = htmlspecialchars($params[$name], ENT_QUOTES, $charset);
			$params[$name] = str_replace("\n", '<br />', $tmp);
		}
		extract($params);
		$traces = implode('</li><li>', $traces);

		$template = <<<HEREDOC
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=$charset"/>
    <title>$message ($code $text)</title>
    <style type="text/css">
    .exception { margin: 0; padding: 0px; margin-top: 30px; background-color: #eee }
    .exception, .exception td, .exception th { font: 11px Verdana, Arial, sans-serif; color: #333 }
    .exception a { color: #333 }
    .exception h1 { margin: 0; margin-top: 4px; font-weight: normal; font-size: 170%; letter-spacing: -0.03em; }
    .exception h2 { margin: 0; padding: 0; font-size: 90%; font-weight: normal; letter-spacing: -0.02em; }
    .exception h3 { margin: 0; padding: 0; margin-bottom: 10px; font-size: 110% }
    .exception ul { padding-left: 20px; list-style: decimal }
    .exception ul li { padding-bottom: 5px; margin: 0 }
    .exception ol { font-family: monospace; white-space: pre; list-style-position: inside; margin: 0; padding: 10px 0 }
    .exception ol li { margin: -5px; padding: 0 }
    .exception ol .selected { font-weight: bold; background-color: #ffd; padding: 2px 0 }
    .exception table.vars { padding: 0; margin: 0; border: 1px solid #999; background-color: #fff; }
    .exception table.vars th { padding: 2px; background-color: #ddd; font-weight: bold }
    .exception table.vars td  { padding: 2px; font-family: monospace; white-space: pre }
    .exception p.error { padding: 10px; background-color: #f00; font-weight: bold; text-align: center; -moz-border-radius: 10px; -webkit-border-radius: 10px; border-radius: 10px; }
    .exception p.error a { color: #fff }
    .exception #main { padding: 20px 25px; margin: 0; margin-bottom: 20px; border: 1px solid #ddd; background-color: #fff; text-align:left; -moz-border-radius: 10px; -webkit-border-radius: 10px; border-radius: 10px; min-width: 770px; max-width: 770px }
    .exception #message { padding: 20px 25px; margin: 0; margin-bottom: 5px; border: 1px solid #ddd; text-align:left; background-color: #c8e8f3; -moz-border-radius: 10px; -webkit-border-radius: 10px; border-radius: 10px; min-width: 770px; max-width: 770px }
    .exception #content { border: 1px solid #ddd; margin-top: 10px; padding: 7px; overflow: auto; }
    .exception a.file_link { text-decoration: none; }
    .exception a.file_link:hover { text-decoration: underline; }
    .exception .code { overflow: auto; }
    .exception img { vertical-align: middle; }
    .exception a img { border: 0; }
    .exception .error { background-color: #f66; padding: 1px 3px; color: #111; }
    </style>
    <script type="text/javascript">
    function toggle(id)
    {
      el = document.getElementById(id); el.style.display = el.style.display == 'none' ? 'block' : 'none';
    }
    </script>
  </head>
  <body>
    <div class="exception">
      <div id="message">
        <div style="float: left; margin-right: 20px">
          <img src="data:image/png;base64,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" />
        </div>
        <div style="float: left; width: 600px">
          <h2>$code $text - $name</h2>
          <h1>$message</h1>
        </div>

        <div style="clear: both"></div>
      </div>

      <div id="main">
        <h3>Stack Trace</h3>

        <ul><li>$traces</li></ul>

        <h3>Content of the Output <a href="#" onclick="toggle('content'); return false;">...</a></h3>

        <div id="content" style="display: none">
        $currentContent
        </div>
        <br/>

        <h3>Request + MVC params <a href="#" onclick="toggle('all_params'); return false;">...</a></h3>

        <div id="all_params" style="display: none">
        $allParams
        </div>
        <br/>

        <div style="clear: both"></div>
      </div>
    </div>
  </body>
</html>
HEREDOC;
        echo $template;
	}
}