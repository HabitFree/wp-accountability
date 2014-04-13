<?php

if (!class_exists("HfAdminPanel")) {
	class HfAdminPanel {
	
		function HfAdminPanel() { //constructor
			// nothing here
		}
		
		function registerAdminPanel() {
			add_menu_page( 'HF Plugin','HF Plugin','activate_plugins','hfAdmin', array( $this, 'generateAdminPanel' ) );
		}
		
		function generateAdminPanel() {
			echo '<h1>HabitFree Admin Panel</h1>';
			
			if(isset($_POST) && array_key_exists('sendTestReportRequestEmail',$_POST)) {
				$Mailer = new HfMailer();
				$Mailer->sendReportRequestEmail(1);
				echo '<p class="success">Test email sent.</p>';
			}
			
			if(isset($_POST) && array_key_exists('sudoReactivateExtension',$_POST)) {
				$HfMain = new HfAccountability();
				$HfMain->sudoReactivateExtension();
				echo '<p class="success">Deactivation and activation functions successfully called.</p>';
			}
			
			echo $this->generateAdminPanelForm();
			
			//$HfMain = new HfAccountability();
			//$nonce = $HfMain->createNonce('duck');
			//echo '[' . $nonce . ']<br />';
			//echo var_dump($HfMain->verifyNonce($nonce, 'duck'));
			
			echo do_shortcode( '[simpletest name="SimpleTest Unit Tests" path="/hf-accountability/php/tests.php"]' );
		}
		
		function generateAdminPanelForm() {
			$HfMain = new HfAccountability();
			$currentURL = $HfMain->getCurrentPageUrl();
			return '<form action="'. $currentURL .'" method="post">
					<p><input type="submit" name="sendTestReportRequestEmail" value="Send test report request email" /></p>
					<p><input type="submit" name="sudoReactivateExtension" value="Sudo reactivate extension" /></p>
				</form>';
		}
	}
}
?>