<?php
class Application_Model_Scripts 
{

    public function getAllScripts($data = array())
    {
        $db = Zend_Registry::get('db');
        if(isset($data['search']) AND $data['search'] != '') $select = $db -> select()
                      -> from('scripts')
					  -> where('MATCH (ScriptName, Description) AGAINST (? IN BOOLEAN MODE)', $data['search']);
		else $select = $db -> select()
                      -> from('scripts');

        if(isset($data['bot']) AND $data['bot'] != 'All') $select -> where('bot = ?', $data['bot']);
        if(isset($data['category']) AND $data['category'] != 'All') $select -> where('category = ?', $data['category']);
                      
					  
		$paginator = Zend_Paginator::factory($select);
		
		$paginator->setCurrentPageNumber(isset($data['page'])?$data['page']:1);
		
		$paginator->setItemCountPerPage(5);
		
        /*$stmt = $db->query($select);
        
        $result = $stmt->fetchAll();        */

        return $paginator;
    }
    
    public function getScript($id) {
        $db = Zend_Registry::get('db');

        $select = $db -> select()
                      -> from('scripts')
                      -> where("scriptID = ?", $id); 
        $stmt = $db->query($select);
        $result = $stmt->fetch();
        
        return $result;
    }
	
	public function getCategories() {
		return Array('Agility' => 'Agility','Combat' => 'Combat','Construction' => 'Construction','Cooking' => 'Cooking','Crafting' => 'Crafting','Dungeoneering' => 'Dungeoneering','Farming' => 'Farming','Firemaking' => 'Firemaking','Fishing' => 'Fishing','Fletching' => 'Fletching','Herblore' => 'Herblore','Hunter' => 'Hunter','Magic' => 'Magic','Minigames' => 'Minigames','Mining' => 'Mining','Misc' => 'Misc','Money Making' => 'Money Making','Prayer' => 'Prayer','Ranged' => 'Ranged','Runecrafting' => 'Runecrafting','Slayer' => 'Slayer','Smithing' => 'Smithing','Summoning' => 'Summoning','Thieving' => 'Thieving','Woodcutting' => 'Woodcutting');
	}
	
	public function getBots() {
		return Array('Powerbot' => 'Powerbot','RSbuddy' => 'RSbuddy', 'Nexus' => 'Nexus');
	}
    
    public function getScriptVersions($id, $subscriber=true)
    {
        $db = Zend_Registry::get('db');
        if($subscriber) {
            $select = $db -> select()
                          -> from('scriptversions')
                          -> where("scriptID = ?", $id)
                          -> order("version DESC");
        }
        else {
            $select = $db -> select()
                          -> from('scriptversions')
                          -> where("scriptID = ?", $id)
                          -> where("lastest != ?", 1)
                          -> order("version DESC");
        }
        $stmt = $db->query($select);
        $result = $stmt->fetchAll();
        
        return $result;
    }
    
    public function getLastest($id)
    {
        $db = Zend_Registry::get('db');

        $select = $db -> select()
                      -> from('scriptversions')
                      -> where("scriptID = ?", $id)
                      -> where("lastest = ?", 1)
                      -> order("version DESC")
                      -> limit("1");

        $stmt = $db->query($select);
        $result = $stmt->fetch();
        
        return $result;
    }
    
    public function getVersion($id)
    {
        $db = Zend_Registry::get('db');

        $select = $db -> select()
                      -> from(array('v' => 'scriptversions'))
                      -> joinLeft(array('s' => 'scripts'),'s.scriptID = v.scriptID')
                      -> where("v.versionID = ?", $id)
                      -> limit("1");

        $stmt = $db->query($select);
        $result = $stmt->fetch();
        
        return $result;
    }
    
    function addScript($data)
    {
        $db = Zend_Registry::get('db');
        $db->insert('scripts', $data);
        return $db->lastInsertId();
    }
    
    function scriptExists($scriptName) {
        $db = Zend_Registry::get('db');
        
        $query = $db->select()->from('scripts', 'count(*)')
                              ->where('scriptName = ?',$scriptName);
        $numRows = $db->fetchOne($query);

        return $numRows;
    }
    
    function editScript($data, $id) {
        $db = Zend_Registry::get('db');
        
        $db->update('scripts', $data, $db->quoteInto('scriptID= ?', $id));   
    }
    
    function delete($id) {
        $db = Zend_Registry::get('db');
        $db->delete('scripts', $db->quoteInto('scriptID= ?', $id));
    }
    
    function addScriptToList($userID, $scriptID) {
        $db = Zend_Registry::get('db');
        $db->insert('scriptlist', array('userID' => $userID,'scriptID' => $scriptID));
    }
    
    function scriptListExists($userID, $scriptID) {
        $db = Zend_Registry::get('db');
        
        $query = $db->select()->from('scriptlist', 'count(*)')
                              ->where('userID = ?',$userID)
                              ->where('scriptID = ?',$scriptID);
        $numRows = $db->fetchOne($query);

        return $numRows;
    }
    
    function deleteScriptList($userID, $scriptID) {
        $db = Zend_Registry::get('db');
        $db->delete('scriptlist', array($db->quoteInto('userID= ?', $userID),$db->quoteInto('scriptID= ?', $scriptID)));
    }
}