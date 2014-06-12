<?php

interface Hf_iUserManager {
    public function userButtonsShortcode();

    public function processAllUsers();

    public function getCurrentUserLogin();

    public function processInvite( $inviteeEmail, $nonce );

    public function getCurrentUserId();

    public function processNewUser( $userId );

    public function sendInvitation( $inviterId, $address, $daysToExpire );
}