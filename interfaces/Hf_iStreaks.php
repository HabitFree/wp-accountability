<?php
if ( ! defined( 'ABSPATH' ) ) exit;
interface Hf_iStreaks {
    public function streaks($goalId, $userId);
}