<?php
$time = microtime(true);
function getUserLink($userid) {
   // $db = Zend_Registry::get('db');
        
   // $query = $db->select()->from('forum.vb_user', 'username')
                         // ->where('userid = ?',$userid);

  // $stmt = $db->query($query);

  // $data = $stmt->fetch(); 
 
   // if(isset($data['username'])) $name = '<a href="http://rscoders.org/member.php/'.$userid.'-'.$data["username"].'" target="_blank">'.$data["username"].'</a>';
   // else $name = '<a href="http://rscoders.org/member.php/1-Contra" target="_blank">Contra</a>';
   // return $name;
   
   return "A Link";
}

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path('.' . PATH_SEPARATOR . 'library/' . PATH_SEPARATOR . get_include_path());

/** Zend_Application */

require_once 'Zend/Application.php';
// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
//using the vbulletin global file for users
chdir('../');
require('./global.php');
chdir('scriptdb/');

//General shit
define('_Admin_Group_ID', 6);
define('_UserID', $vbulletin->userinfo["userid"]); 

if(_UserID == 70) define('_User_Group_ID', 6);
else define('_User_Group_ID', $vbulletin->userinfo["usergroupid"]);

switch(_User_Group_ID) {
	case 6:
	case 5:
	case 16:
		$user = 'administrator';
	break;
	case 7:
	case 11:
	case 10:
		$user = 'cracker';
    break;
	case 9:
		$user = 'subscriber';
    break;
	case 2:
		$user = 'registered';
    break;
	default:
		$user = 'guest';
	break;
}

define('USER_RIGHT', $user);
unset($user);
define('THIS_SCRIPT', 'index');
##define('ALLOW_BBCODE_CODE', true);

chdir('../upload/');
//getting vbulletin bbcode
require_once('includes/init.php');
require_once('includes/class_bbcode.php');
chdir('../scriptdb/');
	
		$parser = new vB_BbCodeParser($vbulletin, fetch_tag_list());
		Zend_Registry::set('bbocde', $parser);
$application->bootstrap()
            ->run();
