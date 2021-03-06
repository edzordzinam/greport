<?php

class Utils_Cast {
	function __construct() {
	}
	
	public static function cast($object, $class) {
		if( !is_object($object) )
			throw new InvalidArgumentException('$object must be an object.');
		if( !is_string($class) )
			throw new InvalidArgumentException('$class must be a string.');
		if( !class_exists($class) )
			throw new InvalidArgumentException(sprintf('Unknown class: %s.', $class));
		if( !is_subclass_of($class, get_class($object)) )
			throw new InvalidArgumentException(sprintf(
					'%s is not a descendant of $object class: %s.',
					$class, get_class($object)
			));
	
		/**
		 * This is a beautifully ugly hack.
		 *
		 * First, we serialize our object, which turns it into a string, allowing
		 * us to muck about with it using standard string manipulation methods.
		 *
		 * Then, we use preg_replace to change it's defined type to the class
		 * we're casting it to, and then serialize the string back into an
		 * object.
		*/
		return unserialize(
				preg_replace(
						'/^O:\d+:"[^"]++"/',
						'O:'.strlen($class).':"'.$class.'"',
						serialize($object)
				)
		);
	}
}

?>