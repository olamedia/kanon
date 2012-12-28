Some documentation draft
===================

Kanon controllers
=================

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
    $this->_runController('mySubController', array('who' => $name)); // run another controller, passing array of options
  }
}
```
Launching controller as application (bootstrap.php)
```
kanon::run('myController');
```



Kanon forms [src/forms]
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
``

