<?php

class HfAdminPanel {

    private $Messenger;
    private $PageLocator;
    private $Database;
    private $UserManager;

    function HfAdminPanel( Hf_iMessenger $Messenger, Hf_iPageLocator $PageLocator, Hf_iDatabase $Database, Hf_iUserManager $UserManager ) {
        $this->Database    = $Database;
        $this->PageLocator = $PageLocator;
        $this->Messenger   = $Messenger;
        $this->UserManager = $UserManager;
    }

    function registerAdminPanel() {
        add_menu_page( 'HF Plugin', 'HF Plugin', 'activate_plugins', 'hfAdmin', array($this, 'generateAdminPanel') );
    }

    function generateAdminPanel() {
        echo '<h1>HabitFree Admin Panel</h1>';

        if ( isset( $_POST ) && array_key_exists( 'sendTestReportRequestEmail', $_POST ) ) {
            $this->Messenger->sendReportRequestEmail( 1 );
            echo '<p class="success">Test email sent.</p>';
        }

        if ( isset( $_POST ) && array_key_exists( 'sendTestInvite', $_POST ) ) {
            $this->UserManager->sendInvitation( 1, 'natethegreat.arthur@gmail.com', 7 );
            echo '<p class="success">Test invite sent.</p>';
        }

        if ( isset( $_POST ) && array_key_exists( 'sudoReactivateExtension', $_POST ) ) {
            $this->Database->sudoReactivateExtension();
            echo '<p class="success">Deactivation and activation functions successfully called.</p>';
        }

        echo $this->generateAdminPanelForm();

        echo do_shortcode( '[simpletest name="SimpleTest Unit Tests" path="/hf-accountability/tests/tests.php" passes="y"]' );
    }

    function generateAdminPanelForm() {
        $currentURL = $this->PageLocator->getCurrentPageURL();
        $Form       = new HfGenericForm( $currentURL );

        $Form->addSubmitButton( 'sendTestReportRequestEmail', 'Send test report request email' );
        $Form->addSubmitButton( 'sendTestInvite', 'Send test invite' );
        $Form->addSubmitButton( 'sudoReactivateExtension', 'Sudo reactivate extension' );

        return $Form->getHtml();
    }

    function addToAdminHead() {
        $cssURL = plugins_url( 'admin.css', dirname( __FILE__ ) );
        echo "<link rel='stylesheet' type='text/css' href='" . $cssURL . "' />";
    }
}
