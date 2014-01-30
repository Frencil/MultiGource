<?php

/*****************************************************
___  ___      _ _   _ _____                          
|  \/  |     | | | (_)  __ \                         
| .  . |_   _| | |_ _| |  \/ ___  _   _ _ __ ___ ___ 
| |\/| | | | | | __| | | __ / _ \| | | | '__/ __/ _ \
| |  | | |_| | | |_| | |_\ \ (_) | |_| | | | (_|  __/
\_|  |_/\__,_|_|\__|_|\____/\___/ \__,_|_|  \___\___|

Author: Christopher Clark (@Frencil)
March 24, 2012

*****************************************************/

/**********************
  USER DEFINED VALUES
**********************/

/* Root path
   EXAMPLE: '/home/chris/repos';
*/
$root_path   = '/Users/eric/Sites/gource';

$ignore = [];

/* Color Regexes
   Gource has some default colors it applies based on file type but
   this can make it hard to tell which repos are which. Here you can
   define regular expressions that will map whole directories to a
   color. It can be a top-level directory of a repo or a subdirectory
   at any depth.
   FORMAT: {REGEX} => {COLOR}
           Where {REGEX} is your directory (from $root_path) with
	   a leading pipe (| - prevents false positives)
	   and {COLOR} is either a six-digit hex (e.g. '#FF0000' or 'c75d39')
	   or a key in the pre-defined color library (e.g. 'main_green').
   EXAMPLES: '/\|big_repos\/big_repo_A\//'       => 'main_green',
             '/\|little_repos\/little_repo_B\//' => '#FF0000',
             '/\|weird_repos\/weird_repo_C\//'   => 'c75d39',
*/
$color_reg = array(
    '!\|mfb\/!'                   => 'main_green',
    '!\|mfb-ap\/!'                => 'main_orange',
    '!\|mfb-api\/!'               => 'main_red',
    '!\|mfb-backend\/!'           => 'main_blue',
    '!\|mfb-bpp\/!'               => 'main_yellow',
    '!\|mfb-gis\/!'               => 'lightest_red',
    '!\|mfb-smi\/!'               => 'lightest_blue',
    '!\|mfbapiclientbundle\/!'    => 'lightest_green',
    '!\|mfbcmsbundle\/!'          => 'lightest_yellow',
    '!\|mfbdatabundle\/!'         => 'main_purple',
    '!\|mfbdatafixturesbundle\/!' => 'darkest_green',
    '!\|mfbdatatypebundle\/!'     => 'lightest_purple',
    '!\|mfbredirectbundle\/!'     => 'darkest_purple',
);

/* Color Library
   Just a handful of colors that look good in Gource.
*/
$color_lib = array('default_color'   => 'F0F0F0',

		   'main_black'      => '454545',

                   'main_red'        => 'F03728',
                   'lighter_red'     => 'F8685D',
                   'lightest_red'    => 'F88E86',
                   'darker_red'      => 'B44C43',
                   'darkest_red'     => '9C170D',
                   
                   'main_orange'     => 'F08828',
                   'lighter_orange'  => 'F8A75D',
                   'lightest_orange' => 'F8BC86',
                   'darker_orange'   => 'B47943',
                   'darkest_orange'  => '9C520D',
                   
                   'main_blue'       => '1B8493',
                   'lighter_blue'    => '4EBAC9',
                   'lightest_blue'   => '6FBEC9',
                   'darker_blue'     => '2B666E',
                   'darkest_blue'    => '095560',
                   
                   'main_green'      => '1FB839',
                   'lighter_green'   => '52DB6A',
                   'lightest_green'  => '77DB88',
                   'darker_green'    => '348A43',
                   'darkest_green'   => '0A771D',
                   
                   'main_yellow'     => 'BFE626',
                   'lighter_yellow'  => 'D4F35B',
                   'lightest_yellow' => 'DCF383',
                   'darker_yellow'   => '97AD41',
                   'darkest_yellow'  => '7A960C',
                   
                   'main_purple'     => '841B93',
                   'lighter_purple'  => 'BA4EC9',
                   'lightest_purple' => 'BE6FC9',
                   'darker_purple'   => '662B6E',
                   'darkest_purple'  => '550960',
                   );



/**********************
  EXECUTABLE CODE
**********************/

$all_commits = array();

slurpLog($root_path);

ksort($all_commits);

$scalar_log = implode($all_commits);

echo $scalar_log;

exit;



/*************************
   FUNCTIONS
 ************************/                

function slurpLog ( $path = '.' ){ 
  
  // Change the working directory
  chdir($path);

  // Scan the dir
  $dir_contents = scandir($path);

  // If dir is a repo then slurp in the log
  if (in_array('.git', $dir_contents)){
    //echo "** Getting log: " . getcwd() . "\n";
    $commits = explode("\n\ncommit ",`git log --name-status`);
    slurpCommits($path, $commits);
  }

  // Otherwise recurse over each subdir
  else {
    $dh = @opendir( $path ); 
    while( false !== ($file = readdir($dh)) ){ 
      if ( !in_array($file, array('.','..')) && is_dir("$path/$file") ){
        slurpLog("$path/$file");
      }
    }
    closedir( $dh ); 
  }

} 

function slurpCommits( $path = '.', $commits = array() ){

  global $all_commits, $color_lib, $color_reg, $root_path;
  
  foreach ($commits as $commit){

    $commit = explode("\n",$commit);
    $files  = array();
    $date   = 0;
    $author = '';

    foreach ($commit as $line){

      // Skip blanks
      if (!trim($line)) continue;

      // Append files
      if ( (substr($line,0,2) == "M\t") || (substr($line,0,2) == "A\t") || (substr($line,0,2) == "D\t")){
          if (!empty($ignore)) {
              foreach ($ignore as $regex){
                  if (preg_match($regex,$line)) continue(2);
              }
          }
        $files[] = substr($line,0,1) . '|' . substr($path,strlen($root_path)+1) . '/' . substr($line,2);
      }

      // Extract author
      else if (substr($line,0,8) == "Author: ") {
        $line_exp = explode(' ',$line);
        $author   = ucwords($line_exp[1]);
      }

      // Extract date
      else if (substr($line,0,8) == "Date:   ") {
        $date = strtotime(substr($line,8));
      }

    }

    // Generate log lines
    foreach ($files as $file){
      $color = $color_lib['default_color'];
      foreach ($color_reg as $regex => $rcolor){
	if (preg_match($regex,'|'.$file)){
	  if (isset($color_lib[$rcolor]))
	    $color = $color_lib[$rcolor];
	  else if (preg_match('/^#?([a-f0-9]{6})$/i',$rcolor))
	    $color = str_replace('#','',$rcolor);
	}
      }
      if (!isset($all_commits[$date]) || !$all_commits[$date]) $all_commits[$date] = '';
      $entry = $date . '|' . $author . '|' . $file . '|' . $color . "\n";
      $all_commits[$date] .= $entry;
    }

  }

}

?>