<?

class KegaAmqp_Amqp_Decimal
{
    public function __construct($n, $e)
    {
        if($e < 0)
            throw new Exception("Decimal exponent value must be unsigned!");
        $this->n = $n;
        $this->e = $e;
    }

    public function asBCvalue()
    {
        return bcdiv($this->n, bcpow(10,$this->e));
    }
}