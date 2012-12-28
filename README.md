Some documentation draft
===================

Kanon controllers
=================
Kanon controllers are hierarchical. Each controller represents some root url (example: /forum/) 
from which it can show index page and process further urls, 
starting from that point (Example: /forum/thread/, /forum/post/ etc). To achieve this, you can use one of the following:

* method _action($action), where $action is the next path component between "/" - for custom url processing; 
* methods actionMyAction and initMyAction() will be processed before any output;
* methods header() and footer() are for page header&footer;
* method showMyAction() will be processed right after all page headers;
* method _initIndex() for index page, see initMyAction()
* method index() for index page, see showMyAction()

In case of nested controllers, order of calls will be following:

```
controller1::initAction() // call controller 2 in this method
controller2::initSomeAction() // call controller 3 in this method
controller3::initSomeOtherAction()
controller1::header() // controller 1
  controller2::header() // controller 2
    controller3::header() // controller 3
    controller3::showSomeOtherAction()
    controller3::footer() // controller 3
  controller2::footer() // controller 2
controller1::footer() // controller 1
```

```
class mySubController extends controller{
  public function index(){
    echo 'Hello, '.htmlspecialchars($this->_options['name']).'!';
  }
}
```
```
class myController extends controller{
  public function actionExample1($name){ // $name will come from $_POST['name'] or $_GET['name']
    $this->_runController('mySubController', array('name' => $name)); // run another controller, passing array of options
  }
}
```
Launching controller as application (bootstrap.php)
```
kanon::run('myController');
```

View component can be implemented, but is not required because logic is already separated from view (init vs show methods).

Kanon forms
===========
```
class myForm extends controlSet{
   
}
```


Using from controller
```
public function initEdit(){
  $this->_form = new myForm(); // creating form
  $this->_form->setItem($myObject); // setting editable object
  if ($this->_form->process()){ // processing form
  		$this->back(); // redirect back to previous page
  }
}
public function showEdit(){
  echo $this->_form->getFormHtml(1); // show form with key 1 (field names will be in form of name="field[1]")
}
```

Kanon models
===============
Somethere at bootstrap:
```
kanon::getModelStorage()
  ->connect('mysql:host=localhost;port=3307;dbname=DATABASE', 'USER', 'PASSWORD')
	;
```

Model example
```
class myModel extends model{ 
    protected $_classes = array( 
            'id' => 'integerProperty', 
            'title' => 'stringProperty', 
            'createdAt' => 'creationTimestampProperty', 
            'modifiedAt' => 'modificationTimestampProperty', 
        ); 
    protected $_fields = array( 
            'id' => 'id', 
            'title' => 'title', 
            'createdAt' => 'created_at', 
            'modifiedAt' => 'modified_at', 
        ); 
    protected $_foreignKeys = array( 
        ); 
    protected $_primaryKey = array( 
            'id', 
        ); 
    protected $_autoIncrement = 'id'; 
    protected $_options = array( 
        ); 
}
```
Other property classes can be found at [src/mvc-model/properties]

Working with model
```
$myModels = myModel::getCollection();
$list = $myModels->select($myModels->createdAt->gte(time()-24*60*60)); // select new models (created in 24 hours)
$model = $list->fetch(); // fetch next model from result list
$list->toArray(); // fetch all models into array
foreach ($list as $model){
  // iterating list
}

$model->title = "some title"; // setting value
echo $model->title->html(); // same as echo htmlspecialchars($model->title->getValue())
$model->save(); // saving changed fields
$model->delete(); // delete by primary key (if primary key exists), or by all values
```
