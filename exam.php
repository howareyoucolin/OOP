<?php

/**
* This class contains public functions for users to compare arrays, find all matches or just the best match.
* And it can also output the whole result of all paths at once using the toString function.
*
* @author     Colin Zhao
* @version    1.0
*/
class PatternMatcher{

	public $pattern = array();
	public $path = array();
	
	/*
	 * Constructor
	 *
	 * It reads the text file line by line, and retrieve the data and store it into the $this->pattern and $this->path arrays.
	 *
	 * @param (string) path of the text file
	 * @return void
	 */
	function __construct($textfile){
	
		//Convert the lines of the file into a string array 
		$file = file($textfile);
		
		//Find out the range of lines for the patterns and paths:
		$pattern_start = 1;
		$pattern_end = $pattern_start + intval(trim($file[0]));
		
		$path_start = $pattern_end + 1;
		$path_end = $path_start + intval(trim($file[$pattern_end]));
		
		//Retrieve data from textfile and store it into the $pattern array and $path array:
		for($i = $pattern_start; $i < $pattern_end; $i++){
			$temp_array = explode(',',trim($file[$i]));
			$this->pattern[count($temp_array)][] = $temp_array;
		}
		
		for($i = $path_start; $i < $path_end; $i++){
			$this->path[] = explode('/',trim(trim($file[$i]),'/'));
		}
		
	}
	
	/*
	 * getMatches
	 *
	 * It reads the text file line by line, and retrieve the data and store it into the $this->pattern and $this->path arrays.
	 *
	 * @param (array) the array of path to look for its ALL matches.
	 * @return array
	 */
	function getMatches($path=array()){
	
		//Initialize an empty array
		$result = array();
		
		//Check if the array of the pattern with a certian length exists
		if(isset($this->pattern[count($path)]) AND !empty($this->pattern[count($path)])){
			
			//loop through the patterns with the same length as the path to find matches and store them into the return array
			foreach($this->pattern[count($path)] as $pattern){
				if($this->isMatched($path,$pattern)){
					$result[] = $pattern;
				}
			}
		}
		return $result;
	}
	
	/*
	 * isMatched
	 *
	 * It checks if the single path and the single pattern matches.
	 *
	 * @param (array,array) the array of a path and a pattern to be compared
	 * @return boolean
	 */
	function isMatched($path,$pattern){
	
		//if the two arrays don't have a same length, there's no need to compare.
		if(count($path) != count($pattern)){
			return false;
		}
		
		//Loop through each item to check if match
		for($i = 0; $i < count($path); $i++){
			if($pattern[$i] != '*' AND $pattern[$i] != $path[$i]){
				return false;
			}
		}
		return true;
	}

	/*
	 * getBestMatch
	 *
	 * It looks for the best match among all matches.
	 *
	 * @param (array) the array of ALL matches
	 * @return array
	 */
	function getBestMatch($result = array()){
		
		//if number of matches is 0, that means there isnt't even match, no need to proceed further.
		if(count($result) == 0){
			return $result;			
		//if number of matches is 1, that is the best and only match.
		}else if(count($result) == 1){
			return $result[0];
		//else it has to compare items into the arrays one by one.
		}else{
			$memo = $result[0];
			for($i = 0 ; $i < count($result)-1 ; $i++){
				if($this->compareMatches($result[$i+1],$result[$i]) == 1){
					//if a better match is found, replace the previous match.
					$memo = $result[$i+1];
				}
			}
			return $memo;
		}
	}
	
	/*
	 * compareMatches
	 *
	 * It compares two matches to see which one is better.
	 *
	 * @param (array) the array of 2 matches to be compared
	 * @return int
	 * -2: error;
	 * -1: match1 < match2;
	 *  0: match1 = match2;
	 *  1: match1 > match2;
	 */
	function compareMatches($match1,$match2){
	
		//Not same length, simply return -2 for error.
		if(count($match1) != count($match2)){
			return -2;
		}
		
		//Check the number of * in 2 arrays, whichever has less *s, the better match.
		$match1_wildcard_count = 0;
		foreach($match1 as $item1){
			if($item1 == '*') $match1_wildcard_count++;
		}
		$match2_wildcard_count = 0;
		foreach($match2 as $item2){
			if($item2 == '*') $match2_wildcard_count++;
		}
		
		if($match1_wildcard_count > $match2_wildcard_count){
			return -1;
		}else if($match1_wildcard_count < $match2_wildcard_count){
			return 1;
			
		//If both have the same number of *s, check further for the order of * appear in the arrays.
		}else{		
			for($i = 0; $i < count($match1); $i++){
				if(($match1[$i] == '*' AND $match2[$i] == '*') OR (($match1[$i] != '*' AND $match2[$i] != '*'))){
					continue;
				}else if($match1[$i] == '*' AND $match2[$i] != '*'){
					return -1;
				}else{
					return 1;
				}
			}		
		}
		return 0;
	}
	
	/*
	 * __toString
	 *
	 * Output the best matches for all items in the $this->path array
	 *
	 * @param (void) 
	 * @return String
	 */
	function __toString(){
		$output = '';
		foreach($this->path as $path){
			if(count($matches = $this->getMatches($path)) == 0){
				$output .= "NO MATCH! \n";
			}else{
				$output .= implode("," , $this->getBestMatch($matches))." \n";
			}
		}
		return $output;
	}

}


/*  EXAMPLE ***************  */

$pm = new PatternMatcher($argv[1]);

echo $pm;

//echo $pm->compareMatches(array('*','*','c'),array('a','*','*')); //-1

