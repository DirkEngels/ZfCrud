<?php

class ZfCrud_Controller extends Zend_Controller_Action
{
    protected function _guessControllerName() {
        preg_match('/([a-zA-Z]+)_(.*)Controller$/', get_class($this), $matches);
        return $matches[2];
    }
    protected function _guessModuleName() {
        preg_match('/([a-zA-Z]+)_(.*)Controller$/', get_class($this), $matches);
        return $matches[1];
    }
    
    public function init()
    {
    	$this->view->object = $this->_guessControllerName();
    	$this->view->module = $this->_guessModuleName();
    }

	public function indexAction ()
	{
		$this->view->title = 'Object Index';
	}
	
	public function adminAction ()
	{		
		Zend_Paginator::setDefaultScrollingStyle('Elastic');
		Zend_View_Helper_PaginationControl::setDefaultViewPartial('_paginator.phtml');

		$modelClass = $this->_guessModuleName() . '_Model_DbTable_' . $this->_guessControllerName();
		$objectModel = new $modelClass();
		$rows = $objectModel->fetchAll();
		$paginator = Zend_Paginator::factory($rows);
		$paginator->setCurrentPageNumber($this->_getParam('page', 1));

		$this->view->paginator = $paginator;
		$this->view->title = 'Admin List';
	}

    public function viewAction ()
    {
        if (false === ($id = $this->_getParam('id', false))) {
            throw new Exception ('Tampered URI');
        }
        
        $modelClass = $this->_guessModuleName() . '_Model_DbTable_' . $this->_guessControllerName();
        $formClass = $this->_guessModuleName() . '_Form_' . $this->_guessControllerName();
        $objectModel = new $modelClass();
        $objectForm = new $formClass();
        $objectRow = $objectModel->fetchRow(
            $objectModel
                ->select()
                ->where('id = ?', $id)
        );
        
        $objectForm->setDefaults($objectRow->toArray());
        
        $this->view->title = 'View object';
        $this->view->form = $objectForm;
        $this->view->id = $id;
    }

    public function newAction ()
    {
        $this->_helper->ViewRenderer->setScriptAction('form');
        
        $modelClass = $this->_guessModuleName() . '_Model_DbTable_' . $this->_guessControllerName();
        $formClass = $this->_guessModuleName() . '_Form_' . $this->_guessControllerName();
        $objectModel = new $modelClass();
        $objectRow = $objectModel->createRow();
        $objectForm = new $formClass();
        $objectForm = $objectForm->process(
        	$this->getRequest()->getPost(),
        	$objectRow
        	
        );

        if (true === $objectForm) {
            $url = '/' . strtolower($this->view->module) . 
                    '/' . strtolower($this->view->object) . 
                    '/saved/';
            $this->_helper->redirector->gotoUrlAndExit($url);
        }
        $this->view->title = 'New object';
        $this->view->form = $objectForm;
    }
 
    public function editAction()
    {
        $this->_helper->ViewRenderer->setScriptAction('form');
        if (false === ($id = $this->_getParam('id', false))) {
            throw new Exception ('Tampered URI');
        }

        $modelClass = $this->_guessModuleName() . '_Model_DbTable_' . $this->_guessControllerName();
        $formClass = $this->_guessModuleName() . '_Form_' . $this->_guessControllerName();
        $objectModel = new $modelClass();
        $objectForm = new $formClass();
        $objectRow = $objectModel->fetchRow(
			$objectModel
    			->select()
                ->where('id = ?', $id)
        );
                        
        $objectForm = $objectForm->process(
        	$this->getRequest()->getPost(), 
        	$objectRow
        );
        if (true === $objectForm) {
            $url = '/' . strtolower($this->view->module) . 
                    '/' . strtolower($this->view->object) . 
                    '/saved/id/' . $id;
            $this->_helper->redirector->gotoUrlAndExit($url);
        }
        
        $this->view->title = 'Edit object';
        $this->view->form = $objectForm;
    }
    public function savedAction()
    {
    	$this->view->title = 'Object saved';
    }
    public function deleteAction()
    {
        if (false === ($id = $this->_getParam('id', false))) {
            throw new Exception ('Tampered URI');
        }

        $modelClass = $this->_guessModuleName() . '_Model_DbTable_' . $this->_guessControllerName();
        $objectModel = new $modelClass();
        $objectModel->delete(
        	$objectModel->getAdapter()->quoteInto('id = ?', $id)
        );

        $this->view->title = 'Object deleted';
    }
}