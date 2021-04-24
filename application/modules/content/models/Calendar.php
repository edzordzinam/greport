<?php

require_once 'Zend/Db/Table/Abstract.php';

class Content_Model_Calendar extends Zend_Db_Table_Abstract
{
		protected $_name = 'calendar';
		
		/***
		 * @author greports.com
		 * @version 1.0
		 * @description : model function to add a new event to the calender table
		 */
		public static function newEvent(){
			
		}
		
		
		/***
		 * @author greports.com
		 * @description : model function to delete an existing calendar event from the table
		 */
		public static function deleteEvent(){
			
		}
		
		
		/***
		 * @author greports.com
		 * @description : model funciton to modify name, place, date and time of the calendar event
		 */
		public static function modifyEvent(){
			
		}
		
		/**
		 * @author greports.com
		 * @description : model to load all events on the calender for a predefined time period..
		 */
		public static function loadCalenderEvents(){
			
		}
}

