<?php

class HfHtmlGenerator implements Hf_iMarkupGenerator {
    private $Cms;

    function __construct( Hf_iContentManagementSystem $ContentManagementSystem ) {
        $this->Cms = $ContentManagementSystem;
    }

    public function progressBar( $percent, $label ) {
        return '<div class="meter">
					<span class="label">' . $label . '</span>
					<span class="progress" style="width: ' . $percent . '%">' . $label . '</span>
				</div>';
    }

    public function generateTabs( $contents, $defaultTabNumber ) {
        $html = '[su_tabs active="' . $defaultTabNumber . '"]';

        foreach ( $contents as $title => $content ) {
            $html .= '[su_tab title="' . $title . '"]' . $content . '[/su_tab]';
        }

        return $this->Cms->expandShortcodes( $html . '[/su_tabs]' );
    }

    public function makeParagraph( $content ) {
        return '<p>' . $content . '</p>';
    }

    public function makeLink( $target, $content ) {
        return '<a href="' . $target . '">' . $content . '</a>';
    }

    public function makeList( $items ) {
        $html = '';
        foreach ( $items as $item ) {
            $html .= '<li>' . $item . '</li>';
        }

        return '<ul>' . $html . '</ul>';
    }

    public function makeError( $content ) {
        return '<p class="error">' . $content . '</p>';
    }

    public function makeSuccessMessage( $content ) {
        return '<p class="success">' . $content . '</p>';
    }
}