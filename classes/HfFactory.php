<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class HfFactory {
    public function makePartnerListShortcode() {
        $UserManager     = $this->makeUserManager();
        $MarkupGenerator = $this->makeMarkupGenerator();
        $AssetLocator    = $this->makeAssetLocator();

        return new HfPartnerListShortcode( $UserManager, $MarkupGenerator, $AssetLocator );
    }

    public function makeUserManager() {
        $Cms          = $this->makeCms();
        $AssetLocator = $this->makeAssetLocator();
        $Mailer       = $this->makeMessenger();
        $Database     = $this->makeDatabase();
        $CodeLibrary  = $this->makeCodeLibrary();

        return new HfUserManager( $Database, $Mailer, $AssetLocator, $Cms, $CodeLibrary );
    }

    public function makeMarkupGenerator() {
        $Cms = $this->makeCms();

        return new HfHtmlGenerator( $Cms );
    }

    public function makeAssetLocator() {
        $Cms = $this->makeCms();

        return new HfUrlFinder( $Cms );
    }

    public function makeCms() {
        return new HfWordPress();
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

    public function makeSecurity() {
        return new HfSecurity();
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
        $MarkupGenerator = $this->makeMarkupGenerator();
        $AssetLocator    = $this->makeAssetLocator();
        $Cms             = $this->makeCms();
        $UserManager     = $this->makeUserManager();

        return new HfAuthenticateShortcode( $MarkupGenerator, $AssetLocator, $Cms, $UserManager );
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

        return new HfGoals( $Mailer, $Cms, $HtmlGenerator, $Database );
    }

    public function makeSettingsShortcode() {
        $AssetLocator = $this->makeAssetLocator();
        $UserManager  = $this->makeUserManager();
        $Security     = $this->makeSecurity();

        return new HfSettingsShortcode( $AssetLocator, $UserManager, $Security );
    }

    public function makeAdminPanel() {
        $Messenger    = $this->makeMessenger();
        $AssetLocator = $this->makeAssetLocator();
        $Database     = $this->makeDatabase();
        $UserManager  = $this->makeUserManager();
        $Cms          = $this->makeCms();

        return new HfAdminPanel( $Messenger, $AssetLocator, $Database, $UserManager, $Cms );
    }
} 