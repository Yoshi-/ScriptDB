<?php
class Application_Model_List
{
    function scriptList($userID, $subscriber) {
        $db = Zend_Registry::get('db');
       

		$query = $db->select()->from(array('l' => 'scriptlist'), array('v.versionID','s.scriptName','v.version','v.mainclass'))
							  ->joinLeft(array('v' => 'scriptversions'),'l.scriptID = v.versionID')
							  ->joinLeft(array('s' => 'scripts'),'s.scriptID = v.scriptID')
                              ->where('userID = ?',$userID)
                              ->where('lastest <= ?',$subscriber);
        $result = $db->fetchAll($query);
        #echo $query ->__toString();
        return $result;
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