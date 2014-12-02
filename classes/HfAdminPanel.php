<?php
if (!defined('ABSPATH')) exit;

class HfAdminPanel {

    private $Messenger;
    private $PageLocator;
    private $Database;
    private $UserManager;
    private $Cms;

    function HfAdminPanel(
        Hf_iMessenger $Messenger,
        Hf_iAssetLocator $PageLocator,
        Hf_iDatabase $Database,
        Hf_iUserManager $UserManager,
        Hf_iCms $ContentManagementSystem
    ) {
        $this->Cms         = $ContentManagementSystem;
        $this->Database    = $Database;
        $this->PageLocator = $PageLocator;
        $this->Messenger   = $Messenger;
        $this->UserManager = $UserManager;
    }

    function registerAdminPanel() {
        $this->Cms->addPageToAdminMenu('HF Plugin', 'hfAdmin', array($this, 'generateAdminPanel'));
    }

    function generateAdminPanel() {
        echo '<h1>HabitFree Admin Panel</h1>';

        if (isset($_POST) && array_key_exists('sendTestReportRequestEmail', $_POST)) {
            $this->Messenger->sendReportRequestEmail(1);
            echo '<p class="success">Test email sent.</p>';
        }

        if (isset($_POST) && array_key_exists('sendTestInvite', $_POST)) {
            $this->UserManager->sendInvitation(1, 'natethegreat.arthur@gmail.com', 7);
            echo '<p class="success">Test invite sent.</p>';
        }

        if (isset($_POST) && array_key_exists('sudoReactivateExtension', $_POST)) {
            $this->sudoReactivateExtension();
            echo '<p class="success">Deactivation and activation functions successfully called.</p>';
        }

        echo $this->generateAdminPanelForm();
    }

    private function sudoReactivateExtension() {
        hfDeactivate();
        hfActivate();
    }

    function generateAdminPanelForm() {
        $currentURL = $this->PageLocator->getCurrentPageURL();
        $Form       = new HfGenericForm($currentURL);

        $Form->addSubmitButton('sendTestReportRequestEmail', 'Send test report request email');
        $Form->addSubmitButton('sendTestInvite', 'Send test invite');
        $Form->addSubmitButton('sudoReactivateExtension', 'Sudo reactivate extension');

        return $Form->getHtml();
    }

    function addToAdminHead() {
        $cssURL = $this->Cms->getPluginAssetUrl('admin.css');
        echo "<link rel='stylesheet' type='text/css' href='" . $cssURL . "' />";
    }
}
