<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestInviteResponseForm extends HfTestCase {
    public function testExtendsForm() {
        $this->assertInstanceOf('HfForm',$this->MockedInviteResponseForm);
    }

    public function testMakesInfoMessage() {
        $needle = '<p class="info">Looks like you\'re responding to an invite. What would you like to do?</p>';
        $this->assertFormOutputContains($needle);
    }

    private function assertFormOutputContains($needle)
    {
        $haystack = $this->MockedInviteResponseForm->getOutput();
        $this->assertContains($needle, $haystack);
    }

    public function testAddsAcceptButtion() {
        $needle = '<p><input type="submit" name="accept" value="Accept invitation" /></p>';
        $this->assertFormOutputContains($needle);
    }

    public function testAddsIgnoreButtion() {
        $needle = '<p><input type="submit" name="ignore" value="Ignore invitation" /></p>';
        $this->assertFormOutputContains($needle);
    }
} 