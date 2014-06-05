<?php

interface Hf_iContentManagementSystem {
    public function getUserEmail($userID);
    public function currentUser();
    public function createUser($username, $email, $password);
}