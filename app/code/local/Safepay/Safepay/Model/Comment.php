<?php
class Safepay_Safepay_Model_Comment{
    public function getCommentText(){ 
        $msg = 'Using webhook secret keys allows Safepay to verify each payment. To get your sandbox webhook key: <br/> 1. Navigate to your Sandbox Safepay dashboard by clicking <a target="__blank" href="https://sandbox.api.getsafepay.com/dashboard/api-settings">here</a> <br/> 2. Activate your Developer settings, copy the webhook secret key and paste into the box above.';
        //$msg += '<br/> tst';
        return $msg;
    }
}