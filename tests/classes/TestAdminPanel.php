<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestAdminPanel extends HfTestCase {
    public function testGenerateAdminPanelButtons() {
        $expectedHtml = '<form action="url" method="post"><p><input type="submit" name="sendTestReportRequestEmail" value="Send test report request email" /></p><p><input type="submit" name="sendTestInvite" value="Send test invite" /></p><p><input type="submit" name="sudoReactivateExtension" value="Sudo reactivate extension" /></p></form>';
        $resultHtml   = $this->mockedAdminPanel->generateAdminPanelForm();

        $this->assertEquals( $expectedHtml, $resultHtml );
    }

    public function testSetsAdminPageIcon() {
        $this->expectOnce(
            $this->mockCms,
            'addPageToAdminMenu',
            array('HF Plugin', 'hfAdmin', array($this->mockedAdminPanel, 'generateAdminPanel'),'dashicons-unlock',3)
        );

        $this->mockedAdminPanel->registerAdminPanel();
    }

    public function testCreatesCustomPostType() {
        $labels = array(
            'name'                => 'Survey Questions',
            'singular_name'       => 'Survey Question',
            'menu_name'           => 'Survey Questions',
            'name_admin_bar'      => 'Survey Questions',
            'parent_item_colon'   => 'Parent Question:',
            'all_items'           => 'All Questions',
            'add_new_item'        => 'Add New Question',
            'add_new'             => 'Add New',
            'new_item'            => 'New Question',
            'edit_item'           => 'Edit Question',
            'update_item'         => 'Update Question',
            'view_item'           => 'View Question',
            'search_items'        => 'Search Question',
            'not_found'           => 'Not found',
            'not_found_in_trash'  => 'Not found in Trash',
        );
        $args = array(
            'label'               => 'Survey Question',
            'description'         => 'Questions displayed after report.',
            'labels'              => $labels,
            'supports'            => array( 'title', 'revisions', 'custom-fields', ),
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => 5,
            'menu_icon'           => 'dashicons-forms',
            'show_in_admin_bar'   => false,
            'show_in_nav_menus'   => false,
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => true,
            'publicly_queryable'  => true,
            'capability_type'     => 'page',
        );
        $this->expectOnce($this->mockCms, 'registerPostType', array('hf_survey_question', $args));

        $this->mockedAdminPanel->registerAdminPanel();
    }
}
