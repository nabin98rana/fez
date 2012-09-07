<?php

/**
 * Interface for all Fez filter admin classes
 * @author uqcmaj
 * @since September 2012
 *
 */
interface Fez_Filter_AdminInterface
{
	/**
	 * Insert or update a filter association for a given input.
	 * @param string $filterClass
	 * @param string $inputName
	 */
	public function save($filterClass, $inputName);
	
	/**
	 * Remove a filter association
	 * @param string $inputName
	 */
	public function delete($inputName);
}