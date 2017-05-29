<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class HfSettingsShortcode implements Hf_iShortcode {
    private $userManager;
    private $pageLocator;
    private $security;

    function __construct( Hf_iAssetLocator $pageLocator, Hf_iUserManager $userManager, Hf_iSecurity $security ) {
        $this->pageLocator = $pageLocator;
        $this->userManager = $userManager;
        $this->security    = $security;
    }

    public function getOutput() {
        if ( is_user_logged_in() ) {
            $html = '[su_tabs]
						[su_tab title="Subscriptions"]' . $this->subscriptionSettings() . '[/su_tab]
						[su_tab title="Account"][wppb-edit-profile][/su_tab]
					[/su_tabs]';

            return do_shortcode( $html );
        } else {
            return $this->security->requireLogin();
        }
    }

    private function subscriptionSettings() {
        $userID  = $this->userManager->getCurrentUserId();
        $message = '';

        if ( isset( $_POST ) && array_key_exists( 'formSubmit', $_POST ) ) {
            $varSubscription = isset( $_POST['accountability'] );
            update_user_meta( $userID, "hfSubscribed", $varSubscription );
            $message = '<p class="success">Your changes have been saved.</p>';
        }

        if ( get_user_meta( $userID, "hfSubscribed", true ) ) {
            $additionalProperties = 'checked="checked"';
        } else {
            $additionalProperties = '';
        }

        $currentURL = $this->pageLocator->getCurrentPageURL();
        $html       = $message . '<form action="' . $currentURL . '" method="post">
					<p><label>
						<input type="checkbox" name="accountability" value="yes" ' . $additionalProperties . ' />
						Keep me accountable by email.
					</label></p>
					<input type="submit" name="formSubmit" value="Save changes" />
				</form>';

        return $html;
    }
}