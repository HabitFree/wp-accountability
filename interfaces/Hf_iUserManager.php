<?php
if ( ! defined( 'ABSPATH' ) ) exit;
interface Hf_iUserManager {
    public function processAllUsers();

    public function getCurrentUserLogin();

    public function processInvite( $inviteeId, $nonce );

    public function getCurrentUserId();

    public function processNewUser( $userId );

    public function sendInvitation( $inviterId, $address );

    public function isUserLoggedIn();

    public function getPartners( $userId );

    public function getUsernameById( $userId );

    public function deleteRelationship($userId1, $userId2);
}