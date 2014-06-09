<?php

interface Hf_iUserManager {
    public function userButtonsShortcode();

    public function processAllUsers();

    public function getCurrentUserLogin();

    public function processInvite( $inviteeID, $nonce );

    public function getCurrentUserId();

    public function processNewUser( $userID );

    public function sendInvitation( $inviterID, $address, $daysToExpire );
}