<?php

class HfFactory {
    public function makeLogInShortcode() {
        $UrlFinder   = $this->makeUrlFinder();
        $CodeLibrary = $this->makeCodeLibrary();
        $Cms         = $this->makeContentManagementSystem();

        return new HfLogInShortcode( $UrlFinder, $CodeLibrary, $Cms );
    }

    public function makeRegisterShortcode() {
        $UrlFinder   = $this->makeUrlFinder();
        $Database    = $this->makeDatabase();
        $CodeLibrary = $this->makeCodeLibrary();
        $Cms         = $this->makeContentManagementSystem();
        $UserManager = $this->makeUserManager();

        return new HfRegisterShortcode( $UrlFinder, $Database, $CodeLibrary, $Cms, $UserManager );
    }

    public function makeGoalsShortcode() {
        $CodeLibrary = $this->makeCodeLibrary();
        $Database    = $this->makeDatabase();
        $UrlFinder   = $this->makeUrlFinder();
        $Messenger   = $this->makeMessenger();
        $UserManager = $this->makeUserManager();
        $Goals       = $this->makeGoals();
        $Security    = $this->makeSecurity();

        return new HfGoalsShortcode( $UserManager, $Messenger, $UrlFinder, $Database, $Goals, $CodeLibrary, $Security );
    }

    public function makeGoals() {
        $WordPressInterface = $this->makeContentManagementSystem();
        $Database           = $this->makeDatabase();
        $HtmlGenerator      = $this->makeHtmlGenerator();
        $Mailer             = $this->makeMessenger();
        $CodeLibrary        = $this->makeCodeLibrary();

        return new HfGoals( $Mailer, $WordPressInterface, $HtmlGenerator, $Database, $CodeLibrary );
    }

    public function makeAdminPanel() {
        $Mailer       = $this->makeMessenger();
        $URLFinder    = $this->makeUrlFinder();
        $DbConnection = $this->makeDatabase();
        $UserManager  = $this->makeUserManager();

        return new HfAdminPanel( $Mailer, $URLFinder, $DbConnection, $UserManager );
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
        $UrlGenerator       = $this->makeUrlGenerator();
        $UrlFinder          = $this->makeUrlFinder();

        return new HfMailer( $UrlFinder, $UrlGenerator, $Security, $Database, $WordPressInterface );
    }

    public function makeDatabase() {
        $WordPressInterface = $this->makeContentManagementSystem();
        $PhpLibrary         = $this->makeCodeLibrary();

        return new HfDatabase( $WordPressInterface, $PhpLibrary );
    }

    public function makeUrlFinder() {
        return new HfUrlFinder();
    }

    public function makeHtmlGenerator() {
        return new HfHtmlGenerator();
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

    public function makeUrlGenerator() {
        return new HfUrlGenerator();
    }
} 