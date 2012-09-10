<?php

/**
 * Interface for filter processor classes
 * @author uqcmaj
 * @since September 2012
 *
 */
interface Fez_Filter_Process_ProcessInterface
{
	/**
	 * Run filters on the object's data
	 */
	public function process(array $data);
}