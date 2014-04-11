<?php

require_once(dirname(__FILE__) . '/../hf-accountability.php');

class UnitWpSimpleTest extends UnitTestCase {
    private $functionsFacade;

    public function __construct() {
    }

    public function setUp() {
    }

    public function testTestingFramework() {
        $this->assertEqual(1, 1);
    }
    
    public function testMainClassExistence() {
    	$this->assertEqual(class_exists("HfAccountability"), True);
    }
    
    public function testMainObjectExistence() {
    	global $HfMain;
    	$this->assertEqual(is_object($HfMain), True);
    }
    
    public function testMainObjectType() {
    	global $HfMain;
    	$this->assertEqual($HfMain instanceof HfAccountability, True);
    }
    
//     public function testGettingCurrentUserLogin() {
//     	$UserManager = new HfUserManager();
//     	$this->assertEqual($UserManager->getCurrentUserLogin(), 'wpdevadmin');
//     }
    
//     public function testAccountabilityShortcodeHandlerReturnValue() {
//     	global $HfMain;
//     	$this->assertEqual($HfMain->subscriptionSettingsShortcode(''), "Hello, there. I'm a shortcode.");
//     }
    
    public function testShortcodeRegistration() {
    	$this->assertEqual(shortcode_exists('hfSettings'), True);
    }
    
    public function testDbDataNullRemoval() {
    	$DbManager = new HfDbManager();
    	$data = array(
				'one' => 'big one',
				'two' => 'two',
				'three' => null,
				'four' => 4,
				'five' => 'null',
				'six' => 0,
				'seven' => false
			);
		
		$expectedData = array(
				'one' => 'big one',
				'two' => 'two',
				'four' => 4,
				'five' => 'null',
				'six' => 0,
				'seven' => false
			);
		
    	$this->assertEqual($DbManager->removeNullValuePairs($data), $expectedData);
    }
    
    public function testTest() {
    }
}

?>