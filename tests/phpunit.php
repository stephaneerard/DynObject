<?php
namespace se\DynObject\Test;
require __DIR__ . '/bootstrap.php';

class PhpunitRunner
{
	public function __construct($suite, $dir)
	{
		$runner = new \PHPUnit_TextUI_TestRunner();
		$suite = new $suite;
		$suite->addTestFiles($this->getTestFiles($dir));
		$runner->doRun($suite, array('verbose'=>false));
	}

	function getTestFiles($dir)
	{
		$files = array();
		$dir = new \DirectoryIterator($dir);
		foreach($dir as $file)
		{
			if(in_array($file->getFilename(), array('.', '..'))) continue;
			if($file->getType() == 'dir')
			{
				$files = array_merge($files, $this->getTestFiles($file->getPathname()));
			}
			elseif(strpos($file->getFilename(), 'Test.php'))
			{
				$files[] = $file->getPathname();
			}
		}
		return $files;
	}
}

new PhpunitRunner(new DynObjectTestSuite(), __DIR__);
