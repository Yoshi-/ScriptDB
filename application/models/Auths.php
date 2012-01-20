<?php
class Application_Model_Auths
{

    public function getAllAuths()
    {
        $db = Zend_Registry::get('db');
        
		$select = $db -> select()
                      -> from('auths');
		
		$stmt = $db->query($select);
        
        $result = $stmt->fetchAll(); 

        return $result;
    }


    function addAuth($data)
    {
        $db = Zend_Registry::get('db');
        $db->insert('auths', $data);
        return $db->lastInsertId();
    }
    
    
    function delete($id) {
        $db = Zend_Registry::get('db');
        $db->delete('auths', $db->quoteInto('authID= ?', $id));
    }
}