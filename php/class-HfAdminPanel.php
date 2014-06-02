<?php

if (!class_exists("HfAdminPanel")) {
	class HfAdminPanel {

        private $Mailer;
        private $URLFinder;
        private $DbConnection;

        function HfAdminPanel($Mailer, $URLFinder, $DbConnection) {
            $this->DbConnection = $DbConnection;
            $this->URLFinder = $URLFinder;
            $this->Mailer = $Mailer;
		}
		
		function registerAdminPanel() {
			add_menu_page( 'HF Plugin','HF Plugin','activate_plugins','hfAdmin', array( $this, 'generateAdminPanel' ) );
		}
		
		function generateAdminPanel() {
			echo '<h1>HabitFree Admin Panel</h1>';

            if(isset($_POST) && array_key_exists('sendTestReportRequestEmail',$_POST)) {
                $this->Mailer->sendReportRequestEmail(1);
				echo '<p class="success">Test email sent.</p>';
			}
			
			if(isset($_POST) && array_key_exists('sudoReactivateExtension',$_POST)) {
                $this->DbConnection->sudoReactivateExtension();
				echo '<p class="success">Deactivation and activation functions successfully called.</p>';
			}
			
			echo $this->generateAdminPanelForm();
			
			echo do_shortcode( '[simpletest name="SimpleTest Unit Tests" path="/hf-accountability/php/tests.php" passes="y"]' );
		}
		
		function generateAdminPanelForm() {
            $currentURL = $this->URLFinder->getCurrentPageURL();
			return '<form action="'. $currentURL .'" method="post">
					<p><input type="submit" name="sendTestReportRequestEmail" value="Send test report request email" /></p>
					<p><input type="submit" name="sudoReactivateExtension" value="Sudo reactivate extension" /></p>
				</form>';
		}
		
		function addToAdminHead() {
			$cssURL = plugins_url( 'admin.css' , dirname(__FILE__) );
			echo "<link rel='stylesheet' type='text/css' href='". $cssURL . "' />";
		}
	}
}
?>