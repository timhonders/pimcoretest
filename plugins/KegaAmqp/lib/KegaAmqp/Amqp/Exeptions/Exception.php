<?

class KegaAmqp_Amqp_Exceptions_Exception extends Exception
{
    public function __construct($reply_code, $reply_text, $method_sig)
    {
        parent::__construct($reply_text,$reply_code);

        $this->amqp_reply_code = $reply_code; // redundant, but kept for BC
        $this->amqp_reply_text = $reply_text; // redundant, but kept for BC
        $this->amqp_method_sig = $method_sig;

        $ms=KegaAmqp_Amqp_Tools::methodSig($method_sig);
        if(array_key_exists($ms, KegaAmqp_Amqp_AbstractChannel::$GLOBAL_METHOD_NAMES))
            $mn = KegaAmqp_Amqp_AbstractChannel::$GLOBAL_METHOD_NAMES[$ms];
        else
            $mn = "";
        $this->args = array(
            $reply_code,
            $reply_text,
            $method_sig,
            $mn
        );
    }
}