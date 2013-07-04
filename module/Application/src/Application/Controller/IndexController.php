<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Console\Request as ConsoleRequest;
use RuntimeException;

class IndexController extends AbstractActionController
{
	protected $sm;
	
    public function indexAction()
    {
    	$username = Null;
    	$user_session = new Container('user');
		if (isset($user_session->username)) {
			$username = $user_session->username;
		}
        return new ViewModel(array(
            'username' => $username,
        ));
    }
    
    public function submitAction()
    {
    	$request = $this->getRequest();
    	if ($request->isPost()) {
    		$username = $this->params()->fromPost('username');
    		if (strlen($username) > 0) {
    			$user_session = new Container('user');
    			$user_session->username = $username;
    		}
    	}
    	return $this->redirect()->toRoute('home');
    }
    
    public function initSessionTableAction()
    {
    	$request = $this->getRequest();

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console!');
        }
    	
    	$query = "
    		CREATE TABLE `session` (
			    `id` CHAR(32) NOT NULL DEFAULT '',
			    `name` VARCHAR(255) NOT NULL,
			    `modified` INT(11) NULL DEFAULT NULL,
			    `lifetime` INT(11) NULL DEFAULT NULL,
			    `data` TEXT NULL,
				PRIMARY KEY (`id`)
			) COLLATE='utf8_general_ci' DEFAULT CHARSET=utf8;
    	";
    	
    	$db = $this->getServiceLocator()->get('db');
    	
    	$statement = $db->query($query);
    	try {
	   		$success = $statement->execute();    		
    	} catch (\Exception $e) {
    		return '[ERROR] ' . $e->getPrevious()->getMessage() . PHP_EOL;
    	}
    	return '[SUCCESS] Session table created.';
    	
    }
}
