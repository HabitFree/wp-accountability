<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once(dirname(dirname(__FILE__)) . '/HfTestCase.php');

class TestFactory extends HfTestCase {
    public function testFactoryMakesObjects() {
        $identifiers = [
            "Goals" => "HfGoals",
            "UserManager" => "HfUserManager",
            "Messenger" => "HfMailer",
            "AssetLocator" => "HfUrlFinder",
            "MarkupGenerator" => "HfHtmlGenerator",
            "Database" => "HfMysqlDatabase",
            "CodeLibrary" => "HfPhpLibrary",
            "Cms" => "HfWordPress",
            "Security" => "HfSecurity",
            "SettingsShortcode" => "HfSettingsShortcode",
            "ManagePartnersShortcode" => "HfManagePartnersShortcode",
            "DependencyChecker" => "HfDependencyChecker",
            "GoalsShortcode" => "HfGoalsShortcode"
        ];

        foreach ( $identifiers as $identifier => $class ) {
            $method = "make$identifier";
            $o = $this->factory->$method();
            $this->assertInstanceOf( $class, $o );
        }
    }

    public function testMakeLoginForm() {
        $f = $this->factory->makeLoginForm('jo');
        $this->assertInstanceOf('HfLoginForm', $f);
    }
}
