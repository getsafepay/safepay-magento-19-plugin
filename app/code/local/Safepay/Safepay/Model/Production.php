<?php
class Safepay_Safepay_Model_Production{
    public function getCommentText(){ 
        $msg = 'Using webhook secret keys allows Safepay to verify each payment. To get your live webhook key: <br/> 1. Navigate to your Live Safepay dashboard by clicking <a  target="__blank" href="https://getsafepay.com/dashboard/api-settings">here</a> <br/> 2. Activate your Developer settings, copy the webhook secret key and paste into the box above.';
        //$msg += '<br/> tst';
        return $msg;
    }
}