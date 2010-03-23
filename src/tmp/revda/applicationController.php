<?php
function startSession($domain, $expire = 360000) {
	session_set_cookie_params($expire, '/', $domain);
	@session_start();
	// Reset the expiration time upon page load
	if (isset($_COOKIE[session_name()])){
		setcookie(session_name(), $_COOKIE[session_name()], time() + $expire, "/", $domain);
	}
}

class zenPager{
	protected $_page = 1;
	protected $_itemsByPage = 10;
	protected $_itemsCount = 0;
	public function setItemsCount($count){
		$this->_itemsCount = $count;
		return $this;
	}
	public function setPage($page){
		$page = intval($page);
		if ($page < 1) $page = 1;
		$this->_page = $page;
		return $this;
	}
	public function getFrom(){
		return ($this->_page-1)*$this->_itemsByPage;
	}
	public function getPage(){
		return $this->_page;
	}
	public function getPagesCount(){
		return ceil($this->_itemsCount/$this->_itemsByPage);
	}
	public function getItemsByPage(){
		return $this->_itemsByPage;
	}
	public function setItemsByPage($itemsByPage){
		$this->_itemsByPage = $itemsByPage;
		return $this;
	}
	public function getHtml(){
		//$page = intval($_GET['page']);
		$h = '';
		if ($this->getPagesCount()>1){
			$h .= '<ul style="display: block; overflow: hidden; margin: 7px 0;">';
			for ($p = 1; $p <= $this->getPagesCount(); $p++){
				if ($p == $this->getPage()){
					$h .= '<li style="display: block; float: left; background: #2994FF; border: solid 1px #ccc; padding: 3px; margin: 3px; font-weight: bold; font-size: 12px;"><a href="?page='.$p.'" style="color: #fff;">'.$p.'</a></li>';
				}else{
					$h .= '<li style="display: block; float: left; background: #eee; border: solid 1px #ccc; padding: 3px; margin: 3px; font-weight: bold; font-size: 12px;"><a href="?page='.$p.'" style="color: #333;">'.$p.'</a></li>';
				}
			}
			$h .= '</ul>';
		}
		return $h;
	}
}

class applicationController extends controller{
	protected $_app = null;
	protected $_user = null;
	protected $_breadcrumb = array();
	protected $_cssIncludes = array();
	protected $_debug = false;
	protected $_pager = null; // pager
	//protected $_placesWrapHtml = array();
	public function getApp(){
		return application::getInstance();
	}
	public function app(){
		//echo memory_get_usage(true).'<br />';
		return $this->getApp();
	}
	/*protected function _wrapPlace($placeCode = null, $prependHtml = '', $appendHtml = ''){
		$this->_placesWrapHtml[$placeCode] = array($prependHtml, $appendHtml);
	}*/
	protected function _getPlaceWidgets($placeCode = null, $options = array()){
		if ($placeCode === null){
			return;
		}
		$controllerName = zenMVC::getFinalControllerName();
		$controllersBreadcrumb = zenMVC::getControllersBreadcrumb();
		$filters = zenMVC::getPlaceFilters();
	}
	public function getClassParents($class=null, $plist=array()) {
		$class = ($class!==null)?$class:$this;
		$parent = get_parent_class($class);
		if($parent) {
			$plist[] = $parent;
			$plist = $this->getClassParents($parent, $plist);
		}
		return $plist;
	}
	protected function _viewPlace($placeCode = null, $options = array()){
		$prependHtml = isset($options['prepend'])?$options['prepend']:'';
		$appendHtml = isset($options['append'])?$options['append']:''; 
		if ($placeCode === null){
			return;
		}
		$controllerName = zenMVC::getFinalControllerName();
		$controllersBreadcrumb = zenMVC::getControllersBreadcrumb();
		$filters = zenMVC::getPlaceFilters();
		$widgets = $this->_getPlaceWidgets($placeCode, $options);
		$widgetsPlacement = $this->getTable('widgets_placement');
		$conditions = $this->getTable('widgets_placement_conditions');
		// get widgets for place
		$parents = $this->getClassParents($controllerName);
		$parents = $controllersBreadcrumb;
		//$in = array();
		//$in[] = $controllerName;
		$in = $parents;
		$in[] = '';
		//$in[] = $controllerName;
		$widgetList = $widgetsPlacement->select()
		->where("$widgetsPlacement->place_code = '".$widgetsPlacement->e($placeCode)."'")
		//->where("$widgetsPlacement->controller_class IN('".implode("','", $in)."')")
		->orderBy("$widgetsPlacement->order ASC")
		;
		$filters = zenMVC::getPlaceFilters();
		if (count($filters)){
			//$widgetList = $widgetList->leftJoin($conditions)->select($conditions);
			//$sqla = array();
			// where ... or ... or ... having count(id) = filters_count
			//$controllerName
			foreach ($filters as $name => $value){
				$filter = $name.'='.$value;
				/*							$conditions->name = '".$conditions->e($name)."' 
						AND $conditions->value = '".$conditions->e($value)."'
*/
				$widgetList = $widgetList
					->where("
					((
						$widgetsPlacement->filter = '$filter'
						AND $widgetsPlacement->controller_class = '$controllerName'
					)OR(
						$widgetsPlacement->child_controllers = 1
						AND	$widgetsPlacement->controller_class IN('".implode("', '", $in)."')
					))");
			}
		}else{
				//$widgetList = $widgetList->leftJoin($conditions)->select($conditions)
				//->where("$widgetsPlacement->controller_class IN('".implode("','", $in)."')")
				$widgetList = $widgetList
				->where("
					((
						$widgetsPlacement->filter = ''
						AND $widgetsPlacement->controller_class = '$controllerName'
					)OR(
						$widgetsPlacement->filter = ''
						AND $widgetsPlacement->child_controllers = 1
						AND	$widgetsPlacement->controller_class IN('".implode("', '", $in)."')
					))
					")
				//->where("$widgetsPlacement->controller_class = $controllerName")
				//->groupBy("$widgetsPlacement->id")
				;
		}
		if (count($widgetList)){
		echo $prependHtml;
		echo '<div class="place place-'.$placeCode.'">';// style="border: solid 1px #e00; overflow: hidden;"
		foreach ($widgetList as $placement){
			//list($placement, $conditions) = $result;
			$class = $placement->widgetClass->getValue();
			$widget = new $class($this);
			//echo $placement->id->html();
			$widget->setPlacement($placement);
			$widget->html();
		}
		echo '</div>';
		echo $appendHtml;
		}
	}
	public function getBasePath($path = null){ // base for website /images/, /css/ etc
		return $this->app()->getBasePath($path);
	}
	public function setTitle($title){
		return $this->app()->setTitle($title);
	}
	public function getTitle(){
		return $this->app()->getTitle();
	}
	public function getBaseUrl(){ // base for website /images/, /css/ etc
		return $this->app()->getBaseUrl();
	}
	public function setDebug($debug = true){
		if (get_class($this) == 'application') {
			$this->_debug = $debug;
			return $this;
		}
		return $this->getApp()->setDebug($debug);
	}
	public function getDebug(){
		if (get_class($this) == 'application') {
			return $this->_debug;
		}
		return $this->getApp()->getDebug();
	}
	public function appendToBreadcrumb($links = array()){
		if (count($links)){
			if (get_class($this) == 'application'){
				foreach ($links as $link){
					$this->_breadcrumb[] = $link;
				}
			}else{
				$app = application::getInstance();
				$app->appendToBreadcrumb($links);
			}
		}
	}
	public function getBreadcrumb(){
		if (get_class($this) == 'application'){
			return $this->_breadcrumb;
		}
		$app = application::getInstance();
		return $app->getBreadcrumb();
	}
	public function viewBreadcrumb(){
		$la = $this->getBreadcrumb();
		//echo 'Breadcrumb: ';
		echo implode(" → ", $la);
	}
	public function __construct(){
		parent::__construct();
		if (get_class($this) == 'application'){
			$this->_app = $this;
		}else{
			$this->_app = application::getInstance();
		}
		$this->_user = isset($_SESSION['user'])?$_SESSION['user']:null;
	}
	public function getUserId(){
		return is_object($this->_user)?$this->_user->id->getValue():0;
	}
	public function getUser(){
		return is_object($this->_user)?$this->_user:0;
	}
	public function getCustomUserPlaces($userId){
		$userPlaces = $this->getTable('user_places');
		$list = $userPlaces->select()->where("$userPlaces->user_id = '".$userId."'");
		return $list;
	}
	public function checkUserPlaceAccess($placePrefix){
		if (!is_object($this->_user)){
			return false;
		}
		$userPlaces = $this->getTable('user_places');
		$access = $userPlaces->select()->where("$userPlaces->place_prefix = '".$userPlaces->e($placePrefix)."'")
		->where("$userPlaces->user_id = '".$this->getUserId()."'")->fetch();
		return $access?true:false;
	}
	public function getUserStatus(){ // 0-new, 1 -old
		return is_object($this->_user)?(($this->_user->createdAt->getValue()<(time()-12*60*60))?1:0):0;
	}
	public function prefHideCancer(){ // 1-hide cancer
		return isset($_SESSION['preferences']['hideCancer'])?$_SESSION['preferences']['hideCancer']:0;
	}
	
	protected function _getTable($table){
		$db = self::_getDB();
		return $db[$table]; 
	}
	public function getTable($table){
		$db = self::_getDB();
		return $db[$table];
	}
	public function css(){
		// echo '.style{}';
	}
	public function getPager($itemsCount){
		//if ($this->_pager == null){
			$this->_pager = new zenPager();
			$this->_pager->setPage($_GET['page'])
								->setItemsCount($itemsCount)
								->setItemsByPage(50);
		//}
		return $this->_pager;
	}
	public function viewPager($itemsCount){
		$pager = $this->getPager($itemsCount);
		echo $pager->getHtml();
		return $pager;
	}
	public function requireCss($uri){
		if (get_class($this) !== 'application') $this->app->requireCss($uri);
		$this->_cssIncludes[] = $uri;
	}
	protected function &_getDB(){
		return application::getDB();
	}
	/*public function __call($name, $arguments){
		if (class_exists($name)){
			return new $name();
		}
	}*/
	protected function _createEml(){
		$eml = new kanonMail();
		$eml->charset = 'utf-8';
		$eml->from = '"Автоответчик olanet.ru" <noreply@olanet.ru>';
		return $eml;
	}
	function back(){
		$this->redirect($_SERVER['HTTP_REFERER']);
	}
	function redirect($url = null, $wait = 0, $html = '', $title = 'Переадресация'){
	$wait = 0;
	if ($this->getDebug()){
		$wait = 60;
	}
	if (!$this->getDebug()){
		if (!$wait) header("Location: ".$url);
	}
	header($_SERVER['SERVER_PROTOCOL']." 303 See Other");
	header("Content-type: text/html; charset=UTF-8");
	echo '<html><head>';
	echo '<title>'.$title.'</title>';
	echo '</head><body onload="doRedirect()" bgcolor="#ffffff" text="#000000" link="#0000cc" vlink="#551a8b" alink="#ff0000">';
	echo '<noscript>';
	echo '<meta http-equiv="refresh" content="'.$wait.'; url=&#39;'.htmlspecialchars($url).'&#39;">';
	echo '</noscript>';
	echo '<p><font face="Arial, sans-serif">Подождите...</font></p>';
	echo '<p><font face="Arial, sans-serif">Если переадресация не сработала, перейдите по <a href="'.$url.'">ссылке</a> вручную.</font></p>';
	echo '<script type="text/javascript" language="javascript">';
	echo 'function doRedirect() {';
	if (!$wait)	echo 'location.replace("'.$url.'");';
	echo '}';
	echo '</script>';
	echo $html;
	echo '</body></html>';
	/*
	 * <html><head><title>Переадресация</title>

	 <meta http-equiv="refresh" content="0; url=&#39;http://www.google.com/accounts/Logout?continue=http://www.google.ru/&#39;"></head>
	 <body bgcolor="#ffffff" text="#000000" link="#0000cc" vlink="#551a8b" alink="#ff0000"><script type="text/javascript" language="javascript">
	 location.replace("http://www.google.com/accounts/Logout?continue\x3dhttp://www.google.ru/")
	 </script></body></html>
	 */
	die();
}
}

class applicationPrototype extends applicationController{
	protected $_relativeBasePath = '/../';
	protected $_basePath = null;
	protected $_baseUrl = '/';
	protected $_title = '';
	public function setBasePath($path){
		$this->_basePath = $path;
	}
	public function setBaseUrl($url){
		$this->_baseUrl = $url;
	}
	public function getBasePath($path = null){
		if ($path !== null){
			return realpath($this->getBasePath().$path).'/';
		}
		if ($this->_basePath === null){
			return realpath(dirname(__FILE__).$this->_relativeBasePath).'/';
		}else{
			return realpath($this->_basePath).'/';
		}
	}
	public function getBaseUrl(){
		return $this->_baseUrl;
	}
	public function getTitle(){
		return $this->_title;
	}
	public function setTitle($title){
		$this->_title = $title;
		return $this;
	}
}