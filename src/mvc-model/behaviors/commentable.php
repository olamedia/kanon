<?php
class commentable extends modelBehavior{
	protected function _getCommentClass(){
		return $this->_modelName.'Comment';
	}
	/**
	 * 
	 * @param model $model
	 */
	public function setUp($model){
		$pk = $model->getPrimaryKey();
		if (count($pk) !== 1){
			throw new Exception('Commentable model must have single primary key');
		}
		$pk = current($pk);
		$model->hasProperty('commentsCount',array(
		'class'=>'integerProperty',
		'field'=>'comments_count',
		));
		$model->hasMethod('getCommentsCollection'); // TODO
		$model->hasMethod('getComments');
		$baseClass = 'commentPrototype';
		model::create($this->_getCommentClass(), $baseClass); // TODO
		$tableName = $model->getCollection()->getTableName().'_comment'; // (s)
		$model->getStorage()->registerCollection($commentClass, $tableName);
	}
	public function getCommentsCollection(){
		return modelCollection::getInstance($this->_getCommentClass());
	}
	/**
	 * 
	 * @param model $model
	 */
	public function getComments($model){
		$comments = $this->getCommentsCollection();
		return $comments->select($comments->parentId->is($model->getPrimaryKeyValue()));
	}
}