<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class HfFactory {
    public function makeManagePartnersShortcode() {
        $Security             = $this->makeSecurity();
        $UserManager          = $this->makeUserManager();
        $PartnerListShortcode = $this->makePartnerListShortcode();
        $InvitePartnerShortcode = $this->makeInvitePartnerShortcode();

        return new HfManagePartnersShortcode( $Security, $UserManager, $PartnerListShortcode, $InvitePartnerShortcode );
    }

    public function makeSecurity() {
        return new HfSecurity();
    }

    public function makeUserManager() {
        $Cms          = $this->makeCms();
        $AssetLocator = $this->makeAssetLocator();
        $Mailer       = $this->makeMessenger();
        $Database     = $this->makeDatabase();
        $CodeLibrary  = $this->makeCodeLibrary();

        return new HfUserManager( $Database, $Mailer, $AssetLocator, $Cms, $CodeLibrary );
    }

    public function makePartnerListShortcode() {
        $UserManager     = $this->makeUserManager();
        $MarkupGenerator = $this->makeMarkupGenerator();
        $AssetLocator    = $this->makeAssetLocator();

        return new HfPartnerListShortcode( $UserManager, $MarkupGenerator, $AssetLocator );
    }

    public function makeCms() {
        return new HfWordPress();
    }

    public function makeAssetLocator() {
        $Cms = $this->makeCms();

        return new HfUrlFinder( $Cms );
    }

    public function makeMessenger() {
        $Cms          = $this->makeCms();
        $Database     = $this->makeDatabase();
        $Security     = $this->makeSecurity();
        $AssetLocator = $this->makeAssetLocator();
        $CodeLibrary  = $this->makeCodeLibrary();

        return new HfMailer( $AssetLocator, $Security, $Database, $Cms, $CodeLibrary );
    }

    public function makeDatabase() {
        $Cms         = $this->makeCms();
        $CodeLibrary = $this->makeCodeLibrary();

        return new HfMysqlDatabase( $Cms, $CodeLibrary );
    }

    public function makeCodeLibrary() {
        return new HfPhpLibrary();
    }

    public function makeMarkupGenerator() {
        $Cms = $this->makeCms();
        $assetLocator = $this->makeAssetLocator();

        return new HfHtmlGenerator( $Cms, $assetLocator );
    }

    public function makeInvitePartnerShortcode() {
        $AssetLocator    = $this->makeAssetLocator();
        $MarkupGenerator = $this->makeMarkupGenerator();
        $UserManager     = $this->makeUserManager();

        return new HfInvitePartnerShortcode( $AssetLocator, $MarkupGenerator, $UserManager );
    }

    public function makeUserButtonsShortcode() {
        $UserManager     = $this->makeUserManager();
        $AssetLocator    = $this->makeAssetLocator();
        $MarkupGenerator = $this->makeMarkupGenerator();

        return new HfUserButtonsShortcode( $UserManager, $AssetLocator, $MarkupGenerator );
    }

    public function makeAuthenticateShortcode() {
        $MarkupGenerator    = $this->makeMarkupGenerator();
        $AssetLocator       = $this->makeAssetLocator();
        $Cms                = $this->makeCms();
        $UserManager        = $this->makeUserManager();
        $LoginForm          = $this->makeLoginForm();
        $RegistrationForm   = $this->makeRegistrationForm();
        $InviteResponseForm = $this->makeInviteResponseForm();

        return new HfAuthenticateShortcode(
            $MarkupGenerator,
            $AssetLocator,
            $Cms,
            $UserManager,
            $LoginForm,
            $RegistrationForm,
            $InviteResponseForm
        );
    }

    public function makeGoalsShortcode() {
        $AssetLocator    = $this->makeAssetLocator();
        $Messenger       = $this->makeMessenger();
        $UserManager     = $this->makeUserManager();
        $Goals           = $this->makeGoals();
        $Security        = $this->makeSecurity();
        $MarkupGenerator = $this->makeMarkupGenerator();
        $CodeLibrary     = $this->makeCodeLibrary();
        $Database        = $this->makeDatabase();

        return new HfGoalsShortcode( $UserManager, $Messenger, $AssetLocator, $Goals, $Security, $MarkupGenerator, $CodeLibrary, $Database );
    }

    public function makeGoals() {
        $Cms           = $this->makeCms();
        $Database      = $this->makeDatabase();
        $HtmlGenerator = $this->makeMarkupGenerator();
        $Mailer        = $this->makeMessenger();
        $codeLibrary = $this->makeCodeLibrary();
        $streaks = $this->makeStreaks();

        return new HfGoals( $Mailer, $Cms, $HtmlGenerator, $Database, $codeLibrary, $streaks );
    }

    public function makeStreaks() {
        $databse = $this->makeDatabase();
        $codeLibrary = $this->makeCodeLibrary();

        return new HfStreaks($databse, $codeLibrary);
    }

    public function makeSettingsShortcode() {
        $AssetLocator = $this->makeAssetLocator();
        $UserManager  = $this->makeUserManager();
        $Security     = $this->makeSecurity();

        return new HfSettingsShortcode( $AssetLocator, $UserManager, $Security );
    }

    public function makeAdminPanel() {
        $actionUrl = $this->getCurrentUrl();

        $markupGenerator = $this->makeMarkupGenerator();
        $messenger    = $this->makeMessenger();
        $assetLocator = $this->makeAssetLocator();
        $database     = $this->makeDatabase();
        $userManager  = $this->makeUserManager();
        $cms          = $this->makeCms();

        return new HfAdminPanel( $actionUrl, $markupGenerator, $messenger, $assetLocator, $database, $userManager, $cms );
    }

    public function makeLoginForm() {
        $actionUrl = $this->getCurrentUrl();
        $markupGenerator = $this->makeMarkupGenerator();
        $cms = $this->makeCms();
        $assetLocator = $this->makeAssetLocator();
        $userManager = $this->makeUserManager();

        return new HfLoginForm($actionUrl, $markupGenerator, $cms, $assetLocator, $userManager);
    }

    public function makeRegistrationForm() {
        $actionUrl = $this->getCurrentUrl();
        $markupGenerator = $this->makeMarkupGenerator();

        return new HfRegistrationForm($actionUrl, $markupGenerator);
    }

    public function makeInviteResponseForm() {
        $actionUrl = $this->getCurrentUrl();
        $markupGenerator = $this->makeMarkupGenerator();

        return new HfInviteResponseForm($actionUrl, $markupGenerator);
    }

    private function getCurrentUrl()
    {
        $assetLocator = $this->makeAssetLocator();
        $actionUrl = $assetLocator->getCurrentPageUrl();
        return $actionUrl;
    }
} 