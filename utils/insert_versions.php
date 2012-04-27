<?php
require(__DIR__.'/../semver.php');
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
if(!$blacklist||!$input) {
	fail('Invalid JSON files!');
}
foreach($blacklist as &$list) {
	$list=str_replace('/', DIRECTORY_SEPARATOR, $list);
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
	foreach($blacklist as $rule) {
		if(preg_match('/^'.str_replace(array('\\*','\\[0-9\\]'),array('.*','[0-9]'),preg_quote($rule,'/')).'/',$file)) {
			fwrite(STDOUT,'Ignoring file '.$file.PHP_EOL);
			continue;
		}
	}
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
