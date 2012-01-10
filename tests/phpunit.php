<?php

namespace se\DynObject\Test;

require 'PHPUnit/Autoload.php';
require 'PHPUnit/Extensions/SeleniumTestCase.php';
require __DIR__ . '/bootstrap.php';

use se\DynObject\Autoloader;

Autoloader::create()->register();

class PhpunitRunner
{
	public function run()
	{
		$files = array();
		$dir = new \DirectoryIterator(__DIR__);
		foreach($dir as $file)
		{
			if($file->getType() != 'file') continue;
			if(strpos($file->getFilename(), 'Test.php'))
			{
				$files[] = $file->getPathname();
			}
		}
		unset($dir);

		$runner = new \PHPUnit_TextUI_TestRunner();

		require __DIR__ . '/DynMethodTestSuite.php';

		$suite = new DynMethodTestSuite();

		$suite->addTestFiles($files);

		$runner->doRun($suite, array('verbose'=>true));
	}
}

$runner = new PhpunitRunner();
$runner->run();
