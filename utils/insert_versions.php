<?php
require('semver.php');
$input=null;
$output=null;
$root=null;
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
	$contents=file_get_contents($file);
	$contents=str_replace('{{{version}}}', $version, $contents);
	file_put_contents($file, $contents);
}
