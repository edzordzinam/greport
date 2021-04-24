<?php

class Content_Model_Menu
{

    protected $_role;
    protected $_items;

    //this is the constructor
    public function Content_Model_Menu($role){

        //initializing the object with the role of the currrent user
        $this->_role = $role;
    }

    private function loadMenu(){
        //initialize the database table to retrieve the list of menu items.
        $this->_items = Content_Model_DbTable_Menu::loadItems($this->_role);

    }


	/**
     * @return the $_items
     */
    public function getItems ()
    {
        $this->loadMenu();
        return $this->_items;
    }

	/**
     * @param field_type $_items
     */
    public function setItems ($_items)
    {
        $this->_items = $_items;
    }


}

