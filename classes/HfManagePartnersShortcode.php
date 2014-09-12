<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class HfManagePartnersShortcode implements Hf_iShortcode {
    private $Security;
    private $UserManager;
    private $PartnerListShortcode;
    private $InvitePartnerShortcode;

    function __construct(
        Hf_iSecurity $Security,
        Hf_iUserManager $UserManager,
        Hf_iShortcode $PartnerListShortcode,
        Hf_iShortcode $InvitePartnerShortcode
    ) {
        $this->Security               = $Security;
        $this->UserManager            = $UserManager;
        $this->PartnerListShortcode   = $PartnerListShortcode;
        $this->InvitePartnerShortcode = $InvitePartnerShortcode;
    }

    public function getOutput() {
        if ( !$this->UserManager->isUserLoggedIn() ) {
            return $this->Security->requireLogin();
        }

        $partnerList = $this->PartnerListShortcode->getOutput();
        $inviteForm  = $this->InvitePartnerShortcode->getOutput();

        return $partnerList . $inviteForm;
    }

} 