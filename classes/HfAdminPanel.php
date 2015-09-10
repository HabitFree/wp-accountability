<?php
if (!defined('ABSPATH')) exit;

class HfAdminPanel extends HfForm {

    private $messenger;
    private $assetLocator;
    private $db;
    private $userManager;
    private $cms;

    public function __construct(
        $actionUrl,
        Hf_iMarkupGenerator $markupGenerator,
        Hf_iMessenger $messenger,
        Hf_iAssetLocator $assetLocator,
        Hf_iDatabase $db,
        Hf_iUserManager $userManager,
        Hf_iCms $cms
    ) {
        $this->elements = array();
        $this->elements[] = '<form action="'.$actionUrl.'" method="post">';

        $this->cms = $cms;
        $this->db = $db;
        $this->assetLocator = $assetLocator;
        $this->messenger = $messenger;
        $this->userManager = $userManager;
    }

    function registerAdminPanel() {
        $this->cms->addPageToAdminMenu('HF Plugin', 'hfAdmin', array($this, 'generateAdminPanel'),'dashicons-unlock');
    }

    function generateAdminPanel() {
        echo '<h1>HabitFree Admin Panel</h1>';

        if (isset($_POST) && array_key_exists('sendTestReportRequestEmail', $_POST)) {
            $this->messenger->sendReportRequestEmail(1);
            echo '<p class="success">Test email sent.</p>';
        }

        if (isset($_POST) && array_key_exists('sendTestInvite', $_POST)) {
            $this->userManager->sendInvitation(1, 'natethegreat.arthur@gmail.com', 7);
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
        $cssURL = $this->cms->getPluginAssetUrl('admin.css');
        echo "<link rel='stylesheet' type='text/css' href='" . $cssURL . "' />";
    }
}
