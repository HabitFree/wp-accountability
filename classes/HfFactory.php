<?php

class HfFactory {
    public function makeAuthenticateShortcode() {
        $DisplayCodeGenerator = $this->makeHtmlGenerator();
        $AssetLocator = $this->makeUrlFinder();

        return new HfAuthenticateShortcode( $DisplayCodeGenerator, $AssetLocator );
    }

    public function makeGoalsShortcode() {
        $Database    = $this->makeDatabase();
        $UrlFinder   = $this->makeUrlFinder();
        $Messenger   = $this->makeMessenger();
        $UserManager = $this->makeUserManager();
        $Goals       = $this->makeGoals();
        $Security    = $this->makeSecurity();
        $Cms         = $this->makeContentManagementSystem();

        return new HfGoalsShortcode( $UserManager, $Messenger, $UrlFinder, $Database, $Goals, $Security, $Cms );
    }

    public function makeGoals() {
        $WordPressInterface = $this->makeContentManagementSystem();
        $Database           = $this->makeDatabase();
        $HtmlGenerator      = $this->makeHtmlGenerator();
        $Mailer             = $this->makeMessenger();

        return new HfGoals( $Mailer, $WordPressInterface, $HtmlGenerator, $Database );
    }

    public function makeAdminPanel() {
        $Mailer       = $this->makeMessenger();
        $URLFinder    = $this->makeUrlFinder();
        $DbConnection = $this->makeDatabase();
        $UserManager  = $this->makeUserManager();
        $Cms          = $this->makeContentManagementSystem();

        return new HfAdminPanel( $Mailer, $URLFinder, $DbConnection, $UserManager, $Cms );
    }

    public function makeSettingsShortcode() {
        $UrlFinder   = $this->makeUrlFinder();
        $UserManager = $this->makeUserManager();
        $Security    = $this->makeSecurity();

        return new HfSettingsShortcode( $UrlFinder, $UserManager, $Security );
    }

    public function makeUserManager() {
        $WordPressInterface = $this->makeContentManagementSystem();
        $UrlFinder          = $this->makeUrlFinder();
        $PhpLibrary         = $this->makeCodeLibrary();
        $Mailer             = $this->makeMessenger();
        $Database           = $this->makeDatabase();

        return new HfUserManager( $Database, $Mailer, $UrlFinder, $WordPressInterface, $PhpLibrary );
    }

    public function makeMessenger() {
        $WordPressInterface = $this->makeContentManagementSystem();
        $Database           = $this->makeDatabase();
        $Security           = $this->makeSecurity();
        $UrlFinder          = $this->makeUrlFinder();

        return new HfMailer( $UrlFinder, $Security, $Database, $WordPressInterface );
    }

    public function makeDatabase() {
        $WordPressInterface = $this->makeContentManagementSystem();
        $PhpLibrary         = $this->makeCodeLibrary();

        return new HfMysqlDatabase( $WordPressInterface, $PhpLibrary );
    }

    public function makeUrlFinder() {
        $Cms = $this->makeContentManagementSystem();

        return new HfUrlFinder($Cms);
    }

    public function makeHtmlGenerator() {
        $Cms = $this->makeContentManagementSystem();

        return new HfHtmlGenerator($Cms);
    }

    public function makeCodeLibrary() {
        return new HfPhpLibrary();
    }

    public function makeContentManagementSystem() {
        return new HfWordPressInterface();
    }

    public function makeSecurity() {
        return new HfSecurity();
    }
} 