<?

class KegaAmqp_Amqp_Tools {
	
	public static function debug_msg($s) {
	  echo $s, "\n";
	}
	
	public static function methodSig($a) {
	    if(is_string($a))
	        return $a;
	    else
	        return sprintf("%d,%d",$a[0] ,$a[1]);
	}
	
	public static function hexdump($data, $htmloutput = true, $uppercase = false, $return = false) {
	    // Init
	    $hexi   = '';
	    $ascii  = '';
	    $dump   = ($htmloutput === true) ? '<pre>' : '';
	    $offset = 0;
	    $len    = strlen($data);
	
	    // Upper or lower case hexidecimal
	    $x = ($uppercase === false) ? 'x' : 'X';
	
	    // Iterate string
	    for ($i = $j = 0; $i < $len; $i++)
	    {
	        // Convert to hexidecimal
	        $hexi .= sprintf("%02$x ", ord($data[$i]));
	
	        // Replace non-viewable bytes with '.'
	        if (ord($data[$i]) >= 32) {
	            $ascii .= ($htmloutput === true) ?
	                            htmlentities($data[$i]) :
	                            $data[$i];
	        } else {
	            $ascii .= '.';
	        }
	
	        // Add extra column spacing
	        if ($j === 7) {
	            $hexi  .= ' ';
	            $ascii .= ' ';
	        }
	
	        // Add row
	        if (++$j === 16 || $i === $len - 1) {
	            // Join the hexi / ascii output
	            $dump .= sprintf("%04$x  %-49s  %s", $offset, $hexi, $ascii);
	            
	            // Reset vars
	            $hexi   = $ascii = '';
	            $offset += 16;
	            $j      = 0;
	            
	            // Add newline            
	            if ($i !== $len - 1) {
	                $dump .= "\n";
	            }
	        }
	    }
	
	    // Finish dump
	    $dump .= $htmloutput === true ?
	                '</pre>' :
	                '';
	    $dump .= "\n";
	
	    // Output method
	    if ($return === false) {
	        echo $dump;
	    } else {
	        return $dump;
	    }
	}

}