<?php

namespace Kambo\Asm;

class ByteVector implements \Countable {
	public $data;	// byte[]
	protected $length;	// int
	public static function constructor__ () 
	{
		$me = new self();
		$me->data = array();

                //$this->length = 64;
		return $me;
	}
	public static function constructor__I ($initialSize) // [final int initialSize]
	{
		$me = new self();
		$me->data = array();
		return $me;
	}

        public function count()
        {
            return count($this->data);
        }

	public function putByte ($b) // [final int b]
	{
            $this->data[] = $b;
            //$this->length = count($this->data);
            return $this;

            /*$length = $this->length;
            if ((($length + 1) > $this->data->length))
            {
                    $this->enlarge(1);
            }
            $this->data[++$length] = $b;
            $this->length = $length;
            return $this;*/
	}

	public function put11 ($b1, $b2) // [final int b1, final int b2]
	{
            $this->data[] = $b1;
            $this->data[] = $b2;
            return $this;

		/*$length = $this->length;
		if ((($length + 2) > $this->data->length))
		{
			$this->enlarge(2);
		}
		$data = $this->data;
		$data[++$length] = $b1;
		$data[++$length] = $b2;
		$this->length = $length;
		return $this;*/
	}
	public function putShort ($s) // [final int s]
	{
            $this->data[] = $this->uRShift($s, 8);
            $this->data[] = $s;

            return $this;

		/*$length = $this->length;
		if ((($length + 2) > $this->data->length))
		{
			$this->enlarge(2);
		}
		$data = $this->data;
		$data[++$length] = $this->uRShift($s, 8);
		$data[++$length] = $s;
		$this->length = $length;
		return $this;*/
	}

        private function uRShift($a, $b)
{
    //if($b == 0) return $a;

    return ($a >> $b & 0xFF);
    //return ($a >> $b) & ~(1<<(8*PHP_INT_SIZE-1)>>($b-1));
}


	public function put12 ($b, $s) // [final int b, final int s]
	{

            $this->data[] = $this->uRShift($b, 0);
            $this->data[] = $this->uRShift($s, 8);
            $this->data[] = $this->uRShift($s, 0);

            return $this;
	}

	public function putInt ($i) // [final int i]
	{
            $this->data[] = $this->uRShift($i, 24);
            $this->data[] = $this->uRShift($i, 16);
            $this->data[] = $this->uRShift($i, 8);
            $this->data[] = $this->uRShift($i, 0);

            return $this;
	}
        
	public function putLong ($l) // [final long l]
	{
            $data = $this->data;
            $i = (int) $this->uRShift($l, 32);
            $data[] = $this->uRShift($i, 24);
            $data[] = $this->uRShift($i, 16);
            $data[] = $this->uRShift($i, 0);
            $data[] = $i;
            $i = (int) $l;
            $data[] = $this->uRShift($i, 24);
            $data[] = $this->uRShift($i, 16);
            $data[] = $this->uRShift($i, 8);
            $data[] = $this->uRShift($i, 0);

            $this->data = $data;
            return $this;
	}
        
	public function putUTF8 ($s) // [final String s]
	{
            $charLength = strlen($s);
            if (($charLength > 65535))
            {
                throw new IllegalArgumentException();
            }


            $data   = $this->data;
            $data[] = $this->uRShift($charLength, 8);
            $data[] = $charLength;

            for ($i = 0; ($i < $charLength); ++$i) {
                $c = ord($this->charAt($s, $i));
                if ((($c >= '001') && ($c <= '177'))) {
                    //$data[] = chr($c);
                    $data[] = $c;
                } else {
                    return $this->encodeUTF8($s, $i, 65535);
                }
            }

            $this->data = $data;

            return $this;
	}

private function charAt($str, $pos)
{
  return $str{$pos};
}

	protected function encodeUTF8 ($s, $i, $maxByteLength) // [final String s, int i, int maxByteLength]
	{
		$charLength = $s->length();
		$byteLength = $i;
		$c = null;
		for ($j = $i; ($j < $charLength); ++$j) 
		{
                        $c = $this->charAt($s, $j);
			if ((($c . '\001') && ($c . '\177')))
			{
				++$byteLength;
			}
			else
				if (($c . '?'))
				{
					$byteLength += 3;
				}
				else
				{
					$byteLength += 2;
				}
		}
		if (($byteLength > $maxByteLength))
		{
			throw new IllegalArgumentException();
		}
		$start = (($this->length - $i) - 2);
		if (($start >= 0))
		{
			$this->data[$start] = $this->uRShift($byteLength, 8);
			$this->data[($start + 1)] = $byteLength;
		}
		if (((($this->length + $byteLength) - $i) > $this->data->length))
		{
			$this->enlarge(($byteLength - $i));
		}
		$len = $this->length;
		for ($j = $i; ($j < $charLength); ++$j) 
		{
			$c = $s->charAt($j);
			if ((($c . '\001') && ($c . '\177')))
			{
				$this->data[++$len] = $c;
			}
			else
				if (($c . '?'))
				{
					$this->data[++$len] = ((0xE0 | (($c >> 12) & 0xF)));
					$this->data[++$len] = ((0x80 | (($c >> 6) & 0x3F)));
					$this->data[++$len] = ((0x80 | ($c & 0x3F)));
				}
				else
				{
					$this->data[++$len] = ((0xC0 | (($c >> 6) & 0x1F)));
					$this->data[++$len] = ((0x80 | ($c & 0x3F)));
				}
		}
		$this->length = $len;
		return $this;
	}

	public function putByteArray ($b, $off, $len) // [final byte[] b, final int off, final int len]
	{
            if (($b != null)) {
                for ($i = $off; $i < $len; $i++) {
                    $this->data[] = $b[$i];
                }
            }

            return $this;
	}

	protected function enlarge ($size) // [final int size]
        {
	}
}
