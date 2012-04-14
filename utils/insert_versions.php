<?php
require('semver.php');
$input=null;
$output=null;
$root=null;
$dry_run=false;
// Get all arguments
while(count($argv) > 0) {
	$arg=array_shift($argv);
	switch($arg) {
		case '-i':
		case '--input':
			$input=array_shift($argv);
			break;
		case '-o':
		case '--output':
			$output=array_shift($argv);
			break;
		case '--root':
			$root=array_shift($argv);
			break;
		case '--dry-run':
			$dry_run=true;
			break;
	}
}
if($root===null) $root='.';
$root=realpath($root);
if($input===null) $input='version';
if($output===null) $output='';
$input=$root.'/'.$input;
$output=$root.'/'.$output;
try {
	$version=new version(file_get_contents($input));
}
catch(versionException $e) {
	fail($e->getMessage());
}
$version=$version->getString();
$dir=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($output));
foreach($dir as $file) {
	$contents1=file_get_contents($file);
	$contents2=str_replace('{{'.'{version}}}', $version, $contents1);
	if($contents1!=$contents2) {
		fwrite(STDOUT,'Writing version information to file '.$file.PHP_EOL);
		if($dry_run) {
			fwrite(STDOUT,'\\_Not writing to disk'.PHP_EOL);
		}
		else {
			file_put_contents($file, $contents2);
		}

	}

}
