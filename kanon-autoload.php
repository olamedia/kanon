<?php
$dirname = dirname(__FILE__).'/';
require_once $dirname.'src/common/kanon.php';
require_once $dirname.'src/common/functions/is_php.php';
kanon::registerAutoload(array(
'CircularReferenceException'=>'src/common/gc/gc.php',
'application'=>'src/mvc-controller/application.php',
'applicationRegistry'=>'src/mvc-controller/applicationRegistry.php',
'booleanProperty'=>'src/mvc-model/properties/booleanProperty.php',
'checkboxInput'=>'src/forms/controls/checkboxInput.php',
'commentPrototype'=>'src/mvc-model/behaviors/models/commentPrototype.php',
'commentable'=>'src/mvc-model/behaviors/commentable.php',
'control'=>'src/forms/control.php',
'controlSet'=>'src/forms/controlSet.php',
'controller'=>'src/mvc-controller/controller.php',
'controllerPrototype'=>'src/mvc-controller/controllerPrototype.php',
'creationTimestampProperty'=>'src/mvc-model/properties/creationTimestampProperty.php',
'dataSource'=>'incubator/data/interfaces/dataSource.php',
'dateInput'=>'src/forms/controls/dateInput.php',
'documentFilenameProperty'=>'src/mvc-model/properties/documentFilenameProperty.php',
'doubleProperty'=>'src/mvc-model/properties/doubleProperty.php',
'embedProperty'=>'src/mvc-model/properties/embedProperty.php',
'eml'=>'src/net/mail/eml.php',
'event'=>'src/events/event.php',
'eventDispatcher'=>'src/events/eventDispatcher.php',
'extendable'=>'src/common/extension/extendable.php',
'extension'=>'src/common/extension/extension.php',
'fileCache'=>'incubator/files/fileCache.php',
'fileInput'=>'src/forms/controls/fileInput.php',
'fileStorage'=>'src/common/fileStorage.php',
'filename'=>'incubator/data/filename.php',
'floatProperty'=>'src/mvc-model/properties/floatProperty.php',
'form'=>'src/forms/form.php',
'frontController'=>'src/mvc-controller/frontController.php',
'gdDriver'=>'src/media/drivers/gdDriver.php',
'htmlTextarea'=>'src/forms/controls/htmlTextarea.php',
'idProperty'=>'src/mvc-model/properties/idProperty.php',
'image'=>'src/media/image.php',
'imageDriver'=>'src/media/imageDriver.php',
'imageFileInput'=>'src/forms/controls/imageFileInput.php',
'imageFilenameProperty'=>'src/mvc-model/properties/imageFilenameProperty.php',
'imagickDriver'=>'src/media/drivers/imagickDriver.php',
'inflector'=>'incubator/inflector/inflector.php',
'inputFile'=>'incubator/simpleStorage/inputFile.php',
'inputResource'=>'incubator/simpleStorage/inputResource.php',
'integerProperty'=>'src/mvc-model/properties/integerProperty.php',
'kanon'=>'src/common/kanon.php',
'kanonExceptionHandler'=>'src/common/handlers/kanonExceptionHandler.php',
'l10n'=>'src/intl/l10n.php',
'l10nLanguage'=>'src/intl/l10nLanguage.php',
'l10nMessage'=>'src/intl/l10nMessage.php',
'l10nWord'=>'src/intl/l10nWord.php',
'listController'=>'src/mvc-controller/listController.php',
'magic'=>'src/common/magic.php',
'mediaFilenameProperty'=>'src/mvc-model/properties/mediaFilenameProperty.php',
'mobile'=>'incubator/mobile/mobile.php',
'model'=>'src/mvc-model/model.php',
'modelAggregation'=>'src/mvc-model/modelAggregation.php',
'modelBehavior'=>'src/mvc-model/modelBehavior.php',
'modelCache'=>'src/mvc-model/cache/modelCache.php',
'modelCollection'=>'src/mvc-model/modelCollection.php',
'modelExpression'=>'src/mvc-model/modelExpression.php',
'modelField'=>'src/mvc-model/modelField.php',
'modelIterator'=>'src/mvc-model/modelIterator.php',
'modelProperty'=>'src/mvc-model/modelProperty.php',
'modelQueryBuilder'=>'src/mvc-model/modelQueryBuilder.php',
'modelResultSet'=>'src/mvc-model/modelResultSet.php',
'modelResultSetIterator'=>'src/mvc-model/modelResultSetIterator.php',
'modelStorage'=>'src/mvc-model/modelStorage.php',
'modificationTimestampProperty'=>'src/mvc-model/properties/modificationTimestampProperty.php',
'module'=>'src/common/module.php',
'mysqlDriver'=>'src/mvc-model/storageDrivers/mysqlDriver.php',
'nokogiri'=>'src/parse/nokogiri.php',
'nullObject'=>'src/common/nullObject.php',
'objectDecorator'=>'incubator/decorator/decorator.php',
'passwordHashProperty'=>'src/mvc-model/properties/passwordHashProperty.php',
'passwordInput'=>'src/forms/controls/passwordInput.php',
'pdoDriver'=>'src/mvc-model/storageDrivers/pdoDriver.php',
'phone'=>'incubator/social/contacts/phone.php',
'plugin'=>'src/common/plugin/plugin.php',
'plugins'=>'src/common/plugin/plugins.php',
'point'=>'src/2d/point.php',
'profiler'=>'src/common/profiler/profiler.php',
'randomHashProperty'=>'src/mvc-model/properties/randomHashProperty.php',
'rectangle'=>'src/2d/rectangle.php',
'registry'=>'src/common/registry.php',
'request'=>'src/common/http/request.php',
'response'=>'src/common/http/response/response.php',
'restClient'=>'src/net/restClient.php',
'ruLanguage'=>'src/intl/ru/ruLanguage.php',
'ruPlural'=>'src/intl/ru/ruPlural.php',
'ruStemmer'=>'src/intl/ru/stemmer.php',
'scaffoldModelCollectionController'=>'incubator/scaffolding/scaffoldCollectionController.php',
'selectControl'=>'src/forms/controls/selectControl.php',
'serviceController'=>'src/mvc-controller/serviceController.php',
'shape'=>'src/2d/shape.php',
'simpleStorage'=>'incubator/simpleStorage/simpleStorage.php',
'simpleStorageBucket'=>'incubator/simpleStorage/simpleStorageBucket.php',
'simpleStorageDriver'=>'incubator/simpleStorage/simpleStorageDriver.php',
'simpleStorageGoogleStorageDriver'=>'incubator/simpleStorage/drivers/simpleStorageGoogleStorageDriver.php',
'simpleStorageInput'=>'incubator/simpleStorage/simpleStorageInput.php',
'simpleStorageLocalDriver'=>'incubator/simpleStorage/drivers/simpleStorageLocalDriver.php',
'simpleStorageObject'=>'incubator/simpleStorage/simpleStorageObject.php',
'storageDriver'=>'src/mvc-model/storageDriver.php',
'storageRegistry'=>'src/mvc-model/storageRegistry.php',
'stringProperty'=>'src/mvc-model/properties/stringProperty.php',
'textInput'=>'src/forms/controls/textInput.php',
'textProperty'=>'src/mvc-model/properties/textProperty.php',
'textarea'=>'src/forms/controls/textarea.php',
'thumbnail'=>'src/media/thumbnail.php',
'thumbnailer'=>'src/media/thumbnailer.php',
'timestampProperty'=>'src/mvc-model/properties/timestampProperty.php',
'timestampable'=>'src/mvc-model/behaviors/timestampable.php',
'uri'=>'src/common/uri.php',
'url'=>'incubator/data/url.php',
'versionProperty'=>'src/mvc-model/properties/versionProperty.php',
'versionableBehavior'=>'src/mvc-model/behaviors/versionableBehavior.php',
'view'=>'src/mvc-view/view.php',
'widgetController'=>'src/mvc-controller/widgetController.php',
'widgets'=>'incubator/widgets/widgets.php',
'yProfiler'=>'src/common/profiler/yProfiler.php',
'yRuInflector'=>'incubator/inflector/yRuInflector.php',
'ySitemap'=>'src/sitemap/ySitemap.php',
'ySitemapIndex'=>'src/sitemap/ySitemapIndex.php',
'ySitemapIndexController'=>'src/sitemap/controllers/ySitemapIndexController.php',
'ySitemapUrl'=>'src/sitemap/ySitemapUrl.php',
'ySitemapUrlSet'=>'src/sitemap/ySitemapUrlSet.php',
'zenMysqlRow'=>'src/mvc-model/compat.php'
),$dirname);
register_shutdown_function(array('kanon', 'onShutdown'));
if (function_exists('spl_autoload_register')){
	spl_autoload_register(array('kanon', 'autoload'));
	spl_autoload_register(array('plugins', 'autoload'));
}else{
	function __autoload($name){
		if (!kanon::autoload($name)){
			plugins::autoload($name);
		}
	}
}
require_once $dirname.'src/common/destroy.func.php';
require_once $dirname.'src/common/functions/create_class.php';
require_once $dirname.'src/common/functions/dataUri.php';
require_once $dirname.'src/common/functions/phpVarCode.php';
require_once $dirname.'src/common/functions/plugin_loaded.php';
require_once $dirname.'src/common/gc/gc.php';
require_once $dirname.'src/common/handlers/kanonErrorHandler.php';
require_once $dirname.'src/common/keep.func.php';
require_once $dirname.'src/mvc-controller/functions/app.php';
require_once $dirname.'src/net/mail.php';
set_exception_handler(array('kanonExceptionHandler', 'handle'));
set_error_handler('kanonErrorHandler');