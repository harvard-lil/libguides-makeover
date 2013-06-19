<?php

# --------- #
# FUNCTIONS #
# --------- #


# Determines whether the input is an int
function isInteger($input)
{
    return(ctype_digit(strval($input)));
}


# Compares two CallNo instances (for sorting)

function cmp_callno($callno1, $callno2)
{
	# Do not compare non-CallNo's
	if ( !($callno1 instanceof callno) || !($callno2 instanceof callno))
		return 0;

	if ($callno1->volume == 0 || $callno2->volume == 0)
	{
		//$callno1->volume = 0;
		//$callno2->volume = 0;
	}

	if ($callno1->callno_new == $callno2->callno_new)
	{
		if ($callno1->cutter_k == $callno2->cutter_k)
		{
			if ($callno1->us_doc == $callno2->us_doc)
			{
				if ($callno1->rock_ref == $callno2->rock_ref)
				{
					if ($callno1->collection == $callno2->collection)
					{
						if ($callno1->prefix == $callno2->prefix)
						{
							if ($callno1->subclass == $callno2->subclass)
							{
								if ($callno1->index_1 == $callno2->index_1)
								{
									if ($callno1->index_2 == $callno2->index_2)
									{
										if ($callno1->index_3 == $callno2->index_3)
											return 0;
										return ($callno1->index_3 < $callno2->index_3) ? -1 : 1;			
									}
									return ($callno1->index_2 < $callno2->index_2) ? -1 : 1;		
								}
								return ($callno1->index_1 < $callno2->index_1) ? -1 : 1;
							}
							return ($callno1->subclass < $callno2->subclass) ? -1 : 1;
						}
						return ($callno1->prefix < $callno2->prefix) ? -1 : 1;
					}
					return ($callno1->collection < $callno2->collection) ? -1 : 1;
				}
				return ($callno1->rock_ref < $callno2->rock_ref) ? -1 : 1;
			}
			return ($callno1->us_doc < $callno2->us_doc) ? -1 : 1;
		}
		return ($callno1->cutter_k < $callno2->cutter_k) ? -1 : 1;
	}
	return ($callno1->callno_new < $callno2->callno_new) ? -1 : 1;
		
}


# --------- #
#  CLASSES  #
# --------- #

# The CallNo class
# Breaks down the CallNo to an easier mathematical representation
# Example: (2-SIZE LG271 K44 K44x 1996)

class callno
{
	public $str_callno; 	# String representation of CallNo (2-SIZE LG271 K44 K44x 1996)
	public $subclass;		# Subclass hash, the first letter (LG271)
	public $index_1;		# The first index after the subclass (K44)
	public $index_2;		# Second index (K44x)
	public $index_3;		# Third index (1996)
	public $volume; 		
	public $collection;		# Whether an item is in a collection or not (0)
	public $size;			# Whether an item is regular or x-SIZE (2)
	public $prefix;
	public $rock_ref;		# Whether it's a RockRef or not
	public $us_doc;			# Whether it's a US DOCS or not
	public $cutter_k;		# Whether it's a CUTTER K or not
	public $old_wid;
	
	function __construct($inp)
	{
		# The actual string representation of the CallNo
		$this->str_callno = $inp;

		$inp = strtoupper($inp);
		$inp = str_replace("."," ", $inp);
		$inp = preg_replace('/\s+/', ' ', $inp);
		
		# NEW ITEMS
		$is_new = explode("NEW ", $inp);
		if (sizeof($is_new) == 1) # Not new
			$this->callno_new = false;
		else
		{
			$this->callno_new = true;
			$inp = $is_new[1];
		}
				
		# CUTTER K's
		$cutter_expl = explode("CUTTER K ", $inp);
		if (sizeof($cutter_expl) == 1) # Not a CUTTER K
			$this->cutter_k = false;
		else
		{
			$this->cutter_k = true;
			$inp = $cutter_expl[1];			
		}
	
		$usdoc_expl = explode("US DOCS ", $inp);
		if (sizeof($usdoc_expl) == 1) # Not a US doc
			$this->us_doc = false;
		else
		{
			$this->us_doc = true;
			$inp = $usdoc_expl[1];
			$this->parse_usdoc($inp);
			return;
		}
	
		# Determine whether the CallNo is a RockRef or not
		$rref_expl = explode("RREF ", $inp);
		if (sizeof($rref_expl) == 1) # Not a rock ref
			$this->rock_ref = 0;
		else
		{
			$this->rock_ref = 1;
			$inp = $rref_expl[1];
		}
	
		# Factor out the COLLECTION factor.
		$coll_expl = explode(" COLLECTION ", $inp);
		if (sizeof($coll_expl) == 1) # No collection
			$this->collection = 0;
		else
		{
			switch ($coll_expl[0])
			{
				case "JAPANESE":
					$this->collection = 1;
					break;
				case "CHINESE":
					$this->collection = 2;
					break;
				case "KOREAN":
					$this->collection = 3;
					break;
				default:
					$this->collection = 0;				
			}
			$inp = $coll_expl[1];
		}
	
		# Check classification system
		$prefix_expl = explode("WID-LC ", $inp);
		if (sizeof($prefix_expl) == 1) # Old Widener
		{
			$this->prefix = 0;
		}
		else
		{
			$this->prefix = 1;
			$inp = str_replace("WID-LC ", "", $inp);
		} 
		
		# Factor out the SIZE factor. 0 means regular size.
		$volume_expl = explode("VOL ", $inp);
		if (sizeof($volume_expl) == 1) # Regular Size
			$this->volume = 0;
		else
		{
			$this->volume = $volume_expl[1];		
			$inp = $volume_expl[0];
		}
		
		# Factor out the SIZE factor. 0 means regular size.
		$size_expl = explode("-SIZE ", $inp);
		if (sizeof($size_expl) == 1) # Regular Size
			$this->size = 0;
		else
		{
			$this->size = $size_expl[0];		
			$inp = $size_expl[1];
		}
		
		if ($this->cutter_k)
		{
			$this->parse_cutter_k($inp);
			return;
		}

		# Parse the rest of the CallNo
		$split = explode(" ", $inp);
		
		# Parse out the subclass
		if($this->prefix == '0')
			$this->subclass = $this->parse_old_subclass($split[0]);
		else
			$this->subclass = $this->parse_subclass($split[0]);
		
		# Parse out the indeces
		$this->index_1 = (sizeof($split) > 1) ? $this->parse_index($inp, 1) : "0000000";
		$this->index_2 = (sizeof($split) > 2) ? $this->parse_index($inp, 2) : "0000000";
		$this->index_3 = (sizeof($split) > 3) ? $this->parse_index($inp, 3) : "0000000";
	}
	
	function parse_cutter_k($inp)
	{
		$cutter_split = explode(" ", $inp);
		
		$subclass = "";
		$subclass_str = $cutter_split[0];
		for ($i=0;$i<strlen($subclass_str);$i++)
		{
			$newletter = ord($subclass_str[$i]);
			if (strlen($newletter) == 2)
				$newletter .= "0";
			$subclass .= $newletter;
		}
		for ($i=strlen($subclass_str);$i<4;$i++)
			$subclass .= "000";
			
		$index = "";
		$index_str = $cutter_split[1];
		for ($i=0;$i<strlen($index_str);$i++)
		{
			$newletter = ord($index_str[$i]);
			if (strlen($newletter) == 2)
				$newletter .= "0";
			$index .= $newletter;
		}
		for ($i=strlen($index_str);$i<3;$i++)
			$index .= "000";
			
		$this->subclass = $subclass;
		$this->index_1 = $index;
		$this->index_2 = 0;
		$this->index_3 = 0;
	}
	
	function parse_usdoc($inp)
	{
		$strpos1 = strpos($inp, ":");
		$strpos2 = strpos($inp, "/");		
		if ( (($strpos1 <= $strpos2)  && ($strpos1 != false)) || (($strpos2 == false) && ($strpos1 != false)) )
			$inp = substr($inp, 0, $strpos1);
		elseif ( (($strpos2 <= $strpos1)  && ($strpos2 != false)) || (($strpos1 == false) && ($strpos2 != false)) )
			$inp = substr($inp, 0, $strpos2);

		$inp = str_replace(" ", "", $inp);			
		
		$dec_expl = explode(".", $inp);
		$subclass_inp = $dec_expl[0];
		$index_inp = $dec_expl[1];
		
		# PARSE THE SUBCLASS
		
		$subclass_string = "";
		for ($i=0;$i<strlen($subclass_inp);$i++)
			$subclass_string .= ord($subclass_inp[$i]);
			
		for ($i=(strlen($subclass_string) / 2);$i<5;$i++)
			$subclass_string .= "00";
			
		$this->subclass = $subclass_string;
			
		# PARSE THE INDEX

		$index_string = "";
		for ($i=0;$i<strlen($index_inp);$i++)
			$index_string .= ord($index_inp[$i]);
			
		for ($i=(strlen($index_string) / 2);$i<5;$i++)
			$index_string .= "00";
			
		$this->index_1 = $index_string;
		$this->index_2 = "0000000";
		$this->index_3 = "0000000";
	}

	function parse_subclass($inp)
	{
		$ret_string = ""; 
	
		$letter = $inp[0]; 
		$ret_string .= (ord($letter) - 64);
	
		# Check if there's a subclass, or null
		if (isInteger($inp[1])) #subclass = null
		{
			$ret_string .= "0000";
			$int_start_index = 1;
		}
		else
		{
			$subclass = (ord($inp[1]) - 64);
			if (strlen($subclass) == 1)
				$subclass = "0" . $subclass;
			$ret_string .= $subclass;
			if (isInteger($inp[2])) # No secondary subclass (KJD)
			{
				$ret_string .= "00";
				$int_start_index = 2;
			}
			else			
			{
				$subclass = (ord($inp[2]) - 64);
				if (strlen($subclass) == 1)
					$subclass = "0" . $subclass;
				$ret_string .= $subclass;			
				$int_start_index = 3;
			}
		}
		
		$pref = "";
		$int_part = substr($inp, $int_start_index, strlen($inp) - $int_start_index);
		
		$dec_split = explode(".", $int_part);
		if (sizeof($dec_split) > 0)
			$before_dec = $dec_split[0];
		if (sizeof($dec_split) > 1)
			$after_dec = $dec_split[1];
			
			
		if (isset($before_dec))    	
		{
			for ($i=0;$i<(4 - strlen($before_dec));$i++)
			{
				$pref = "0" . $pref;
			}
			$pref .= $before_dec;
		}
		
		$ret_string .= $pref;
		if (isset($after_dec))
			$ret_string .= "." . $after_dec;
	
	#	for ($i=$int_start_index;$i<strlen($inp);$i++)
	#		$ret_string .= $inp[$i];
		
		return $ret_string;
	}
	
	function parse_old_subclass($inp)
	{
		$ret_string = ""; //echo $inp . '<br />';
		
		if(isInteger($inp)){
			return $inp;
		}
		else {
		preg_match("/[A-Z]+/", $inp, $matches);
		$letters = $matches[0];
		$letters = substr($letters, 0, 3);
		preg_match("/[0-9\.]+/", $inp, $matches);
		$numbers = $matches[0];
		
		//echo $letters . ' + ' . $numbers;
		
		$inp = $letters . $numbers;
	
		$letter = $inp[0];
		$ret_string .= (ord($letter) - 64);
	
		# Check if there's a subclass, or null
		if (isInteger($inp[1])) #subclass = null
		{
			$ret_string .= "0000";
			$int_start_index = 1;
		}
		else
		{
			$subclass = (ord($inp[1]) - 64);
			if (strlen($subclass) == 1)
				$subclass = "0" . $subclass;
			$ret_string .= $subclass;
			if (isInteger($inp[2])) # No secondary subclass (KJD)
			{
				$ret_string .= "00";
				$int_start_index = 2;
			}
			else			
			{
				$subclass = (ord($inp[2]) - 64);
				if (strlen($subclass) == 1)
					$subclass = "0" . $subclass;
				$ret_string .= $subclass;			
				$int_start_index = 3;
			}
		}
		
		$pref = "";
		$int_part = substr($inp, $int_start_index, strlen($inp) - $int_start_index);
		
		$dec_split = explode(".", $int_part);
		if (sizeof($dec_split) > 0)
			$before_dec = $dec_split[0];
		if (sizeof($dec_split) > 1)
			$after_dec = $dec_split[1];
			
			
		if (isset($before_dec))    	
		{
			for ($i=0;$i<(4 - strlen($before_dec));$i++)
			{
				$pref = "0" . $pref;
			}
			$pref .= $before_dec;
		}
		
		$ret_string .= $pref;
		if (isset($after_dec))
			$ret_string .= "." . $after_dec;
	
	#	for ($i=$int_start_index;$i<strlen($inp);$i++)
	#		$ret_string .= $inp[$i];
		//echo '<br />';
		return $ret_string;
		}
	}

	function parse_index($inp, $ind)
	{
		# echo "Parsing index: ".$inp."<br/>";
		# xx-xxxx-x
		# letter-number-xbit
	
		$ret_string = "";
	
		$divided = explode(" ",$inp);
	
		$current = $divided[$ind];
		if (ord($current[0]) >= ord('A') && ord($current[0]) <= ord('Z'))
		{
			$subclass = (ord($current[0]) - 64);
			if (strlen($subclass) == 1)
				$subclass = "0" . $subclass;
			$ret_string .= $subclass;
			$num_start = 1;
		}
		else
		{
			$ret_string .= "00";
			$num_start = 0;
		}
	
		if ($current[strlen($current)-1] == 'x' || $current[strlen($current)-1] == 'X')
		{        	
			$current = substr($current, 0, strlen($current)-1);
			$x_bit = 1;
		}
		else
			$x_bit = 0;
	
		$iter_end = 5 - (1 - $num_start);
		for ($i=$num_start;$i<$iter_end;$i++)
		{
			if ($i>=strlen($current))
				$ret_string .= "0";
			else
				$ret_string .= $current[$i];
		}
			  
		if ($x_bit)
			$ret_string .= "1";
		else
			$ret_string .= "0";
	  
		return $ret_string;
	}    
	
}

?>