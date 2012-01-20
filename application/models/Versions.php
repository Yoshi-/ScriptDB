<?php
class Application_Model_Versions 
{
    function versionExists($scriptID, $versionNumber) {
        $db = Zend_Registry::get('db');
        
        $query = $db->select()->from('scriptversions', 'count(*)')
                              ->where('scriptID = ?',$scriptID)
                              ->where('version = ?',$versionNumber);
        $numRows = $db->fetchOne($query);
        //echo $query ->__toString();
        return $numRows;
    }
    
    function addVersion($data) {
        $db = Zend_Registry::get('db');
        
        /*if($data['lastest'] == 1)
        {
            $db->update('scriptversions', array('lastest' => 0), $db->quoteInto('scriptID= ?', $data['scriptID']));   
        }*/
        
        $db->insert('scriptversions', $data);
    } 
	
    function editVersion($data) {
        $db = Zend_Registry::get('db');
        
        /*if($data['lastest'] == 1)
        {
            $db->update('scriptversions', array('lastest' => 0), $db->quoteInto('scriptID= ?', $data['scriptID']));   
        }*/

		$db->update('scriptversions', $data, $db->quoteInto('versionID= ?', $data['versionID']));  
    } 
    
    function delete($id) {
        $db = Zend_Registry::get('db');
        $db->delete('scriptversions', $db->quoteInto('versionID= ?', $id));
    }
}