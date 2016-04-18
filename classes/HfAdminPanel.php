<?php
if (!defined('ABSPATH')) exit;

class HfAdminPanel extends HfForm {

    private $Messenger;
    private $PageLocator;
    private $Database;
    private $UserManager;
    private $Cms;

    public function __construct(
        $actionUrl,
        Hf_iMarkupGenerator $markupGenerator,
        Hf_iMessenger $Messenger,
        Hf_iAssetLocator $PageLocator,
        Hf_iDatabase $Database,
        Hf_iUserManager $UserManager,
        Hf_iCms $ContentManagementSystem
    ) {
        $this->elements = array();
        $this->elements[] = '<form action="'.$actionUrl.'" method="post">';

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

        $currentUserId = $this->UserManager->getCurrentUserId();

        if (isset($_POST) && array_key_exists('sendTestReportRequestEmail', $_POST)) {
            $this->Messenger->sendReportRequestEmail($currentUserId);
            echo '<p class="success">Test email sent.</p>';
        }

        if (isset($_POST) && array_key_exists('sendTestInvite', $_POST)) {
            $address = $this->Cms->getUserEmail($currentUserId);
            $this->UserManager->sendInvitation($currentUserId, $address);
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
        $this->addSubmitButton('sendTestReportRequestEmail', 'Send test report request email');
        $this->addSubmitButton('sendTestInvite', 'Send test invite');
        $this->addSubmitButton('sudoReactivateExtension', 'Sudo reactivate extension');

        return $this->getOutput();
    }

    function addToAdminHead() {
        $cssURL = $this->Cms->getPluginAssetUrl('admin.css');
        echo "<link rel='stylesheet' type='text/css' href='" . $cssURL . "' />";
    }
}
