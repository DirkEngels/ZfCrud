<?php

class ZfCrud_Form extends Zend_Form
{
    private function _guessFormName() {
        preg_match('/([a-zA-Z]+)_(.*)Form$/', get_class($this), $matches);
        return $matches[2];
    }
    private function _guessModuleName() {
        preg_match('/([a-zA-Z]+)_(.*)Form$/', get_class($this), $matches);
        return $matches[1];
    }
	
	
    /**
    * Set up form fields, filtering and validation
    */
    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
		$this->setAction($_SERVER['REQUEST_URI']);
		
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setLabel('Submit');
		$this->addElement($submit);
    }
    
    protected function _initValidators(Zend_Db_Table_Row $row)
    {
    	
    }
    public function process (array $post, Zend_Db_Table_Row $row)
    {
        $this->setDefaults($row->toArray());

		$this->_initValidators($row);
		
		$objectId = null;
        if (sizeof($post) && $this->isValid($post)) {
        	try {
                $row->setFromArray($this->getValues());
                $objectId = $row->save();
            } catch (Exception $e) {
                //$this->addDescription('There was an error saving your details');
                return false;
            }

	        if ($objectId>0) {
				// Handle File uploads
				foreach($this->getElements() as $element) {
					if ($element->getType() === 'Zend_Form_Element_File') {
						$this->_handleFileUpload($element->getName(), $objectId);
					}
				}
				return true;
	        }
        }
        
        
        return $this;
    }

	protected function _handleFileUpload($fileName, $objectId)
	{
		/* Uploading Document File on Server */
		$upload = new Zend_File_Transfer_Adapter_Http();
		$upload->setDestination("/tmp/");
		try {
			// upload received file(s)
			$upload->receive();
		} catch (Zend_File_Transfer_Exception $e) {
			$e->getMessage();
		}

		// you MUST use following functions for knowing about uploaded file
		# Returns the file name for 'doc_path' named file element
		$name = $upload->getFileName($fileName);
		
		// New Code For Zend Framework :: Rename Uploaded File
		$renameFile = strtolower($this->_guessModuleName()) . 
		      '/' . strtolower($this->_guessFormName()) . 
		      '/' . $objectId . '.jpg';
		$fullFilePath = 'public/uploads/'.$renameFile;
		// Rename uploaded file using Zend Framework
		$filterFileRename = new Zend_Filter_File_Rename(array('target' => $fullFilePath, 'overwrite' => true));
		
		$filterFileRename->filter($name);
		
	}
}