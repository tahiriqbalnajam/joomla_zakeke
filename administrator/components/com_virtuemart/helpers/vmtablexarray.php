<?php
/**
 * Xref table abstract class to create tables specialised doing xref
 *
 * The pkey is the Where key in the load function,
 * the skey is the select key in the load function
 *
 *
 * @package	VirtueMart
 * @subpackage Helpers
 * @author Max Milbers
 * @copyright Copyright (c) 2011 - 2021 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */

defined('_JEXEC') or die();

class VmTableXarray extends VmTable {

	/** @var int Primary key */

	protected $_autoOrdering = false;
	protected $_orderable = false;
    protected $_skey = '';
    protected $_skeyForm = '';


	function setSecondaryKey($key,$keyForm=0){
		$this->_skey 		= $key;
		$this->{$key}			= array();
		$this->_skeyForm	= empty($keyForm)? $key:$keyForm;

    }

	function setOrderableFormname($orderAbleFormName){
		$this->_okeyForm = $orderAbleFormName;
	}


    /**
     * Records in this table are arrays. Therefore we need to overload the load() function.
     * TODO, this function is giving back the array, not the table, it is not working like the other table, so we should change that
     * for the 2.2. at least.
	 * @author Max Milbers
     * @param int $id
     */
    function load($oid=null,$overWriteLoadName=0,$andWhere=0,$tableJoins= array(),$joinKey = 0){

    	if(empty($this->_skey) ) {
    		vmError( 'No secondary keys defined in VmTableXarray '.$this->_tbl );
    		return false;
    	}

		if($this->_orderable){
			$orderby = 'ORDER BY `'.$this->_orderingKey.'`';
		} else {
			$orderby = '';
		}

		$pkey = $this->_pkey;
		$skey = $this->_skey;
		$this->{$pkey} = $oid;

		if ($andWhere === 0) $andWhere = '';

		$hash = crc32((int)$oid. $skey . $this->_tbl . $pkey . $orderby);

		if (!isset (self::$_cache['ar'][$hash])) {
			$q = 'SELECT `'.$skey.'` FROM `'.$this->_tbl.'` WHERE `'.$pkey.'` = "'.(int)$oid.'" '.$andWhere.' '.$orderby;
			$this->_db->setQuery($q);
			$result = $this->_db->loadColumn();
			if(!$result){
				//vmError(get_class( $this ).':: load'  );
				self::$_cache['ar'][$hash] = false;
			} else {
				if(empty($result)) $result = array();
				if(!is_array($result)) $result = array($result);
				self::$_cache['ar'][$hash] = $result;
			}
		}

		$this->{$skey} = self::$_cache['ar'][$hash];

		return self::$_cache['ar'][$hash];

    }

    /**
     * This binds the data to this kind of table. You can set the used name of the form with $this->skeyForm;
     *
     * @author Max Milbers
     * @param array $data
     */
	public function bind($data, $ignore = array()){

		$this->_update = null;

		if(!empty($data[$this->_tbl_key])){
			$this->{$this->_tbl_key} = $data[$this->_tbl_key];
		}

		if(!empty($data[$this->_pkeyForm])){
			$this->{$this->_pkey} = $data[$this->_pkeyForm];
		}

		if(!empty($data[$this->_skeyForm])){
			$this->{$this->_skey} = $data[$this->_skeyForm];
		}

		if($this->_orderable){
			$orderingKey = $this->_orderingKey;
			if(!empty($data[$orderingKey])){
				$this->{$orderingKey} = $data[$this->_orderingKey];
			}
		}

		return true;

	}

	public function check(){

		foreach ($this->_obkeys as $obkeys => $error) {
			if (empty($this->{$obkeys})) {
				$error = get_class($this) . ' ' .vmText::sprintf('COM_VIRTUEMART_STRING_ERROR_OBLIGATORY_KEY', 'COM_VIRTUEMART_' . strtoupper($obkeys) );
				vmError($error);
				return false;
			}
		}

		if ($this->_unique) {
			if (empty($this->_db)) $this->_db = JFactory::getDBO();
			foreach ($this->_unique_name as $obkeys => $error) {

				if (empty($this->{$obkeys})) {
					$error = vmText::sprintf('COM_VIRTUEMART_STRING_ERROR_NOT_UNIQUE_NAME', 'COM_VIRTUEMART_' . strtoupper($obkeys));
					vmError('Non unique ' . $this->_unique_name . ' ' . $error);
					return false;
				} else {

					$valid = $this->checkCreateUnique($this->_tbl, $obkeys);
					if (!$valid) {
						return false;
					}
				}
			}
		}

		$this->convertDec();

		return true;
	}

    /**
     *
     * @author Max Milbers, George Kostopoulos
     * @see libraries/joomla/database/JTable#store($updateNulls)
     */
    public function store($updateNulls = false) {

    	$returnCode = true;
		$this->setLoggableFieldsForStore();
		$db = JFactory::getDBO();

        $pkey = $this->_pkey;
        $skey = $this->_skey;
        $tblkey = $this->_tbl_key;

        // We select all database rows based on our _pkey
        $q  = 'SELECT * FROM `'.$this->_tbl.'` WHERE `'.$pkey.'` = "'. $this->{$pkey}.'" ';
        $db->setQuery($q);
        $objList = $db->loadObjectList();

        // We convert the database object list that we got in a more friendly array
        $oldArray = null;
        if($objList) {
            foreach($objList as $obj){
                $oldArray[] = array($pkey=>$obj->{$pkey}, $skey=>$obj->{$skey});
            }
        }

        // We make another database object list with the values that we want to insert into the database
	    $svalue = $this->{$skey};
        $newArray = array();
		if(!empty($svalue)){
	            if(!is_array($svalue)) $svalue = array($svalue);
	            foreach($svalue as $value) $newArray[] = array($pkey=>$this->{$pkey}, $skey=>$value);
		}

        // Inserts and Updates
        if(count($newArray)>0){
            $myOrdering = 1;

            foreach ($newArray as $newValue) {
                // We search in the existing (old) rows to find one of the new rows we want to insert
                $result = $this->array_msearch($oldArray, $newValue);

                // We start creating the row we will insert or update
                $obj = new stdClass;
                $obj->{$pkey} = $newValue[$pkey];
                $obj->{$skey} = $newValue[$skey];

                if($this->_autoOrdering){
                    $oKey = $this->_orderingKey;
                    $obj->{$oKey} = $myOrdering++;
                }

                // If the new row does not exist in the old rows, we will insert it
                if( $result === false ) {
                    $returnCode = $db->insertObject($this->_tbl, $obj, $pkey);
                }
                else {
                    // If the new row exists in the old rows, we will update it
                    $obj->{$tblkey} = $objList[$result]->{$tblkey};
                    $returnCode = $db->updateObject($this->_tbl, $obj, $tblkey);
                }
            }
        }
        else {
            // There are zero new rows, so the user asked for all the rows to be deleted
            $q  = 'DELETE FROM `'.$this->_tbl.'` WHERE `' . $pkey.'` = "'. $this->{$pkey} .'" ';
            $db->setQuery($q);

            try{
	            $db->execute();
            } catch (Exception $e){
	            $returnCode = false;
	            vmError(get_class( $this ).':: store '.$e->getMessage());
            }
        }


        // Deletions
        if(!empty($oldArray)) {
            for ($i = 0; $i < count($oldArray); $i++) {
                $result = $this->array_msearch($newArray, $oldArray[$i]);

                // If no new row exists in the old rows, we will delete the old rows
                if( $result === false ) {
                    // If the old row does not exist in the new rows, we will delete it
                    $q  = 'DELETE FROM `'.$this->_tbl.'` WHERE `' . $tblkey.'` = "'. $objList[$i]->{$tblkey} .'" ';
                    $db->setQuery($q);

	                try{
		                $db->execute();
	                } catch (Exception $e){
		                $returnCode = false;
		                vmError(get_class( $this ).':: store '.$e->getMessage());
	                }
                }
             }
        }

 	return $this->{$skey};

    }

    /**
     *
     * Searches in an array of arrays to find a specific array we want
     *
     * @author George Kostopoulos
     * @param source array of arrays that we will search
     * @param the target array we want to find
     */
    protected function array_msearch($parents, $searched) {
        if (empty($searched) || empty($parents)) {
            return false;
        }

        foreach ($parents as $key => $value) {
            $exists = true;
            foreach ($searched as $skey => $svalue) {
                $exists = ($exists && IsSet($parents[$key][$skey]) && $parents[$key][$skey] == $svalue);
            }
            if($exists){ return $key; }
         }

        return false;
    }


    function deleteRelation(){
		$db = JFactory::getDbo();
    	$q  = 'DELETE FROM `'.$this->_tbl.'` WHERE `'.$this->_pkey.'` = "'. $this->{$this->_pkey}.'" ';
    	$db->setQuery($q);
	    try{
		    $db->execute();
	    } catch (Exception $e){
		    vmError(get_class( $this ).':: store '.$e->getMessage(),'Couldnt delete relations');
		    return false;
	    }

    	return true;
    }

	function loadOrderingCurrentItem( $cid, $orderingkey, $array = 0 ) {

		$svalue = $this->{$this->_skeyForm};
		if(is_array($svalue)){
			$svalue = reset($svalue);
		}

		$q = $q = 'SELECT `' . $orderingkey . '` FROM `' . $this->_tbl . '` WHERE `' . $this->_pkeyForm . '` = "' . (int)$cid . '" AND '.$this->_skeyForm.' = "'.$svalue.'" limit 0,1 ';
		$this->_db->setQuery($q);
		try{
			$this->{$orderingkey} = $this->_db->loadResult();
			vmdebug('vmTableXarray Move loaded ordering of current item ',$q, $orderingkey, $this->{$orderingkey});
		} catch (Exception $e){
			vmError(get_class($this) .' '. $e->getMessage());
		}

	}

}