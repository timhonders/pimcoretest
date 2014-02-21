<?

class KegaAmqp_Amqp_Exceptions_ChannelException extends KegaAmqp_Amqp_Exception
{
    public function __construct($reply_code, $reply_text, $method_sig)
    {
        parent::__construct($reply_code, $reply_text, $method_sig);
    }

}