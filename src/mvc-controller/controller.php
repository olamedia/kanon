<?php
/**
 * $Id$
 */
#require_once dirname(__FILE__).'/controllerPrototype.php';
#require_once dirname(__FILE__).'/applicationRegistry.php';
class controller extends controllerPrototype{
	protected $_startTime = null;
	public function __construct(){
		$this->_startTime = microtime(true);
		parent::__construct();
	}
	public function getTable($tableName){ // Compatibility with zenMysql2 ORM
		$storage = kanon::getModelStorage();
		foreach ($storage->getRegistry()->modelSettings as $modelName => $settings){
			if ($settings['table'] == $tableName){
				return kanon::getCollection($modelName);
			}
		}
		return false;
	}
	public function getRegistry(){
		return applicationRegistry::getInstance();
	}
	public function getApplication(){
		application::getInstance();
	}
	public function app(){
		return $this->getApplication();
	}
	public function registerMenuItem($title, $action){
		$this->getRegistry()->menu->{get_class($this)}[$title] = $action;
	}
	public function getMenu(){
		return $this->getRegistry()->menu->{get_class($this)};
	}
	/**
	 * Set base path for /images/, /css/ etc
	 * @param string $path
	 */
	public function setBasePath($path){
		$this->getRegistry()->basePath = $path;
		return $this;
	}
	public function getBasePath($path = null){
		if ($path !== null){
			return realpath($this->getBasePath().$path).'/';
		}
		if ($this->getRegistry()->basePath === null){
			return realpath(dirname(__FILE__).$this->_relativeBasePath).'/';
		}else{
			return realpath($this->getRegistry()->basePath).'/';
		}
	}
	/**
	 * Set html page <title>
	 * @param string $title
	 * @return controller
	 */
	public function setTitle($title){
		$this->getRegistry()->title = $title;
		return $this;
	}
	public function getTitle(){
		return $this->getRegistry()->title;
	}
	public function appendToBreadcrumb($links = array()){
		if (is_array($links)){
			if (count($links)){
				foreach ($links as $link){
					$this->getRegistry()->breadcrumb[] = $link;
				}
			}
		}else{
			$this->getRegistry()->breadcrumb[] = $links;
		}
		return $this;
	}
	public function getBreadcrumb(){
		return $this->getRegistry()->breadcrumb->toArray();
	}
	public function viewBreadcrumb(){
		if (count($this->getBreadcrumb()) > 1){
			echo '<div class="app_breadcrumb">'.implode(" → ", $this->getBreadcrumb()).'</div>';
		}
	}
	public function getUser(){
		static $user;
		$user = isset($_SESSION['site_user'])?$_SESSION['site_user']:null;
		return $user;
	}
	public function getUserId(){
		return is_object($this->getUser())?$this->getUser()->id->getValue():0;
	}
	public function requireCss($uri, $order = 0){
		$this->getRegistry()->cssIncludes->{'order'.$order}[] = $uri;
	}
	public function css($cssString){
		$this->getRegistry()->plainCss[] = $cssString;
	}
	public function robots($text){
		$this->getRegistry()->meta->robots[] = $text;
	}
	public function getMeta(){
		$h = '';
		if (count($this->getRegistry()->meta)){
			foreach ($this->getRegistry()->meta as $name => $meta){
				if (count($meta) > 1){
					$meta = implode(',',$meta);
				}
				$h .= '<meta name="'.$name.'" content="'.$meta.'" />';
			}
		}
		return $h;
	}
	public function getCss(){
		$h = '';
		if (count($this->getRegistry()->plainCss)){
			$h .= '<style type="text/css">';
			$h .= $this->getRegistry()->plainCss;
			$h .= '</style>';
		}
		return $h;
	}
	public function js($jsString, $alias = 'default', $require = ''){
		$this->getRegistry()->plainJs->{$alias} .= $jsString;
		$this->getRegistry()->plainJsRequire->{$alias} = $require;
	}
	public function requireJs($uri, $alias = 'default', $require = ''){
		$this->getRegistry()->javascriptIncludes->{$alias}[] = $uri;
		$this->getRegistry()->javascriptIncludesRequire->{$alias} = $require;
	}
	protected function _getJsPart($requiredPart, $parts = array()){
		$includes =  $this->getRegistry()->javascriptIncludes->toArray();
		$includesRequire = $this->getRegistry()->javascriptIncludesRequire->toArray();
		//var_dump($includes);
		//var_dump($includesRequire);
		$plainJs = $this->getRegistry()->plainJs->toArray();
		$plainJsRequire = $this->getRegistry()->plainJsRequire->toArray();
		//var_dump($plainJs);
		//var_dump($plainJsRequire);
		//$parts = array();
		$js = '';
		if (is_array($requiredPart) || $requiredPart != ''){
			if (is_array($requiredPart)){
				foreach ($requiredPart as $alias){
					list($xjs, $xparts) = $this->_getJsPart($alias, $parts);
					$parts = array_merge($parts, $xparts);
					$js .= $xjs;
				}
			}elseif(!in_array($requiredPart, $parts)){
				$urls = array();
				$plain = '';
				$includeRequire = 'none';
				if (isset($includes[$requiredPart])){
					$urls = $includes[$requiredPart];
					$includeRequire = $includesRequire[$requiredPart];
				}
				if (isset($plainJs[$requiredPart])){
					$plain = $plainJs[$requiredPart];
					$includeRequire = $plainJsRequire[$requiredPart];
				}
				//var_dump($includeRequire);
				$includeRequireString = is_array($includeRequire)?implode(",",$includeRequire):$includeRequire;
				$js .= '<!-- start '.$requiredPart.' ('.$includeRequireString.') -->';

				if (is_array($includeRequire) || $includeRequire != ''){
					list($xjs, $xparts) = $this->_getJsPart($includeRequire, $parts);
					$parts = array_merge($parts, $xparts);
					$js .= $xjs;
				}

				foreach ($urls as $url){
					$js .= '<script type="text/javascript" src="'.$url.'"></script>';
				}
				if (strlen($plain)){
					$js .= '<script type="text/javascript">'.$plain.'</script>';
				}
				$js .= '<!-- finish '.$requiredPart.' ('.$includeRequireString.') -->';
			}
		}
		$parts[] = $requiredPart;
		return array($js, $parts);
	}
	protected function _getJs(){
		$js = '';
		$includes =  $this->getRegistry()->javascriptIncludes->toArray();
		$plainJs = $this->getRegistry()->plainJs->toArray();
		$parts = array();
		foreach ($includes as $alias => $urls){
			list($xjs, $xparts) = $this->_getJsPart($alias, $parts);
			$parts = array_merge($parts, $xparts);
			$js .= $xjs;
		}
		foreach ($plainJs as $alias => $pjs){
			list($xjs, $xparts) = $this->_getJsPart($alias, $parts);
			$parts = array_merge($parts, $xparts);
			$js .= $xjs;
		}
		return $js;
	}
	public function head(){
		echo $this->getHeadContents();
	}
	public function getHeadContents(){
		$h = '<!DOCTYPE html>'; // html5
		$h .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		$h .= '<title>'.$this->getTitle().'</title>';
		$h .= $this->getMeta();
		if (count($this->getRegistry()->cssIncludes)){
			$includes = $this->getRegistry()->cssIncludes->toArray();
			sort($includes);
			foreach ($includes as $order => $urls){
				foreach ($urls as $url){
					$h .= '<link rel="stylesheet" type="text/css" href="'.$url.'" />';
				}
			}
		}
		$h .= $this->getCss();
		$h .= $this->_getJs();
		/*if (count($this->getRegistry()->javascriptIncludes)){
			foreach ($this->getRegistry()->javascriptIncludes as $url){
			$h .= '<script type="text/javascript" src="'.$url.'"></script>';
			}
			}
			if (count($this->getRegistry()->plainJs)){
			foreach ($this->getRegistry()->plainJs as $plainJs){
			$h .= '<script type="text/javascript">';
			$h .= $plainJs;
			$h .= '</script>';
			}
			}*/
		$h .= '<link rel="shortcut icon" href="/favicon.ico" />';
		return $h;
	}
	protected function &getDatabase($name = null){
		if ($name === null){
			return $this->getRegistry()->defaultDatabase;
		}
		if (!is_array($this->getRegistry()->databases)){
			$this->getRegistry()->databases = array();
		}
		return isset($this->getRegistry()->databases[$name])?$this->getRegistry()->databases[$name]:null;
	}
	public function viewPages($pagesCount, $selectedPage){
		if ($pagesCount < 2) return;
		echo '<div class="pages">';
		$la = array();
		if ($selectedPage>1){
			$la[] = '<a href="?page='.($selectedPage-1).'" class="prev">предыдущая</a>';
		}
		$p = 0;
		while ($p<=$pagesCount){
			$p++;
			//echo $p.' ';
			if ($p>0 && $p<=$pagesCount){
				//echo ' ok';
				if ($p==$selectedPage){
					$la[] = '<b>'.$p.'</b>';
				}else{
					$la[] = '<a href="?page='.$p.'">'.$p.'</a>';
				}
			}
		}
		if ($selectedPage<$pagesCount){
			$la[] = '<a href="?page='.($selectedPage+1).'" class="next">следующая</a>';
		}
		echo implode(' ',$la);
		echo '</div>';
	}
}