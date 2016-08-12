<?php
if ( ! defined( 'ABSPATH' ) ) exit;
interface Hf_iHealth {
    public function getHealth($goalId, $userId);
}