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

    function registerCustomPostTypes() {
        $this->registerSurveyQuestionCustomPostType();
    }

    function registerAdminPages() {
        $this->cms->addPageToAdminMenu('HF Plugin', 'hfAdmin', array($this, 'generateAdminPanel'),'dashicons-unlock',3);
    }

    function generateAdminPanel() {
        echo '<h1>HabitFree Admin Panel</h1>';

        $currentUserId = $this->userManager->getCurrentUserId();

        if (isset($_POST) && array_key_exists('sendTestReportRequestEmail', $_POST)) {
            $this->messenger->sendReportRequestEmail($currentUserId);
            echo '<p class="success">Test email sent.</p>';
        }

        if (isset($_POST) && array_key_exists('sendTestInvite', $_POST)) {
            $address = $this->cms->getUserEmail($currentUserId);
            $this->userManager->sendInvitation($currentUserId, $address);
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
        echo "<link rel='stylesheet' type='text/css' href='$cssURL' />";
    }

    private function registerSurveyQuestionCustomPostType()
    {
        $labels = array(
            'name' => 'Survey Questions',
            'singular_name' => 'Survey Question',
            'menu_name' => 'Survey Questions',
            'name_admin_bar' => 'Survey Questions',
            'parent_item_colon' => 'Parent Question:',
            'all_items' => 'All Questions',
            'add_new_item' => 'Add New Question',
            'add_new' => 'Add New',
            'new_item' => 'New Question',
            'edit_item' => 'Edit Question',
            'update_item' => 'Update Question',
            'view_item' => 'View Question',
            'search_items' => 'Search Question',
            'not_found' => 'Not found',
            'not_found_in_trash' => 'Not found in Trash',
        );
        $args = array(
            'label' => 'Survey Question',
            'description' => 'Questions displayed after report.',
            'labels' => $labels,
            'supports' => array('title', 'revisions'),
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-forms',
            'show_in_admin_bar' => false,
            'show_in_nav_menus' => false,
            'can_export' => true,
            'has_archive' => true,
            'exclude_from_search' => true,
            'publicly_queryable' => true,
            'capability_type' => 'page',
        );

        $this->cms->registerPostType('hf_survey_question', $args);
    }
}
