<?php
require('semver.php');
//Defaults
$input='package.json';
$output='';
$root='.';
$dry_run=false;
$blacklist='version.blacklist.json';
$shell=NULL;
// Get all arguments
while(count($argv) > 0) {
	$arg=array_shift($argv);
	switch($arg) {
		case '-p':
		case '--package':
			$input=array_shift($argv);
			break;
		case '-s':
		case '--source':
			$output=array_shift($argv);
			break;
		case '-b':
		case '--base':
			$root=array_shift($argv);
			break;
		case '--dry-run':
			$dry_run=true;
			break;
		case '--blacklist':
			$blacklist=array_shift($argv);
			break;
		case '--shell':
			$shell=array_shift($argv);
	}
}
//Add root paths
$input=$root.'/'.$input;
$output=$root.'/'.$output;
$blacklist=$root.'/'.$blacklist;
//Read those JSON files
if(!file_exists($input)) fail('Package file does not exist');
$input=json_decode(file_get_contents($input),true);
if(file_exists($blacklist)) {
	$blacklist=json_decode(file_get_contents($blacklist),true);
}
else {
	$blacklist=array();
}
//Process blacklist
foreach($blacklist as &$entry) {
	$entry=realpath($root.'/'.$entry);
}
//Initialize the version from package file
try {
	$version=new version($input['version']);
}
catch(versionException $e) {
	fail($e->getMessage());
}
$version=$version->getString();
$dir=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($output));
foreach($dir as $file) {
	if(preg_match('/[\\\\\\/]\\./', $file)) continue; //Ignore . directories
	if(in_array(realpath($file), $blacklist)) continue;
	$contents1=file_get_contents($file);
	$contents2=str_replace(array('{{{version}}}','{{{'.'version}}}'), $version, $contents1);
	if($contents1!=$contents2) {
		fwrite(STDOUT,'Writing version information to file '.$file.PHP_EOL);
		if($shell!==null){
			system($shell.' "'.$file.'"',$exit_code);
			if($exit_code!=0) fail('Subshell exited '.$exit_code);
		}
		if($dry_run) {
			fwrite(STDOUT,'\\_Not writing to disk'.PHP_EOL);
		}
		else {
			file_put_contents($file, $contents2);
		}

	}

}
function fail($message='') {
	fwrite(STDERR,$message.PHP_EOL);
	exit(1);
}
