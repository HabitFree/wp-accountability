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

    public function makeErrorMessage( $content ) {
        return '<p class="error">' . $content . '</p>';
    }

    public function makeSuccessMessage( $content ) {
        return '<p class="success">' . $content . '</p>';
    }

    public function makeQuoteMessage( $quotation ) {
        return '<p class="quote">"' . $quotation->post_content . '" — ' . $quotation->post_title . '</p>';
    }

    public function makeInfoMessage( $content ) {
        return '<p class="info">' . $content . '</p>';
    }

    public function makeForm( $url, $content, $name ) {
        return '<form action="' . $url . '" method="post" name="' . $name . '">' . $content . '</form>';
    }

    public function makeButton( $name, $label, $onclick ) {
        return '<input type="button" name="' . $name . '" value="' . $label . '" onclick="' . $onclick . '" />';
    }

    public function makeHiddenField( $name ) {
        return '<input type="hidden" name="' . $name . '" />';
    }
}