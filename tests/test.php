<?php

$obj = DynamicObject::instanciate();

$obj
->property('name')
	->withGetter()
	->withSetter()
	->getObject()
->property('Address')
	->withGetter()
	->withSetter()
	->setType('Address')
	->getObject()
->method('say')
	->implementation(function(DynamicObjectInterface $object, $sentence){
		echo sprintf('%s: %s', $object->property('name')->get(), $sentence);
	}, 'default', true)
	->implementation(function(DynamicObjectInterface $object, $sentence){
		$object
			->method('say')
				->setCurrentImplementation('default')
				->call(strtoupper($sentence));
	}, 'capsall')
	->getObject()
->method('hello')
	->implementation(function(DynamicObjectInterface $object, $name){
		$object->method('say')->call(sprintf('Hello %s', $name));
	}, 'default', true)
	->implementation(function(DynamicObjectInterface $object, $name){
		$object
			->method('say')
				->setCurrentImplementation('capsall')
				->call(sprintf('Hello %s', $name));
	}, 'badass')
;

$obj
	->property('name')
		->set('Stéphane')
;

if($obj->get('name') == 'Stéphane')
{
	echo PHP_EOL . ' $obj name = Stéphane' . PHP_EOL;
}

$obj->set('name', 'Prouteur Fou');
if($obj->get('name') == 'Prouteur Fou')
{
	echo PHP_EOL . ' $obj name = Prouteur Fou' . PHP_EOL;
}

$test_call = function() use($obj){
	$obj
		->method('hello')
			->setCurrentImplementation('default')
			->call('Stéphane')
			->setCurrentImplementation('badass')
			->call('Stéphane')
	;
};

$test_call();

echo PHP_EOL . 'LISTENERS' . PHP_EOL;

$obj->method('hello')->listener('after', 'call', function(){echo PHP_EOL;}, 'eol');

$test_call();

echo PHP_EOL . 'LISTENERS FOR FILTERING' . PHP_EOL;

$obj->method('hello')->listener('before', 'call', function(DynamicMethodInterface $method, $args){
	if($method->getCurrentImplementation() == 'badass')
	{
		$args[0] = 'CONNARD';
	}
	return $args;
}, 'badword');

$test_call();



echo PHP_EOL . 'TEST SETTING PROPERTY WITH CLASS TYPE';

$obj->set('Address', new Address('76 Boulevard des Champs-Elysées'));
$address = $obj->get('Address');
echo ($address instanceof Address ? 'OK' : 'NOT OK');

try{
	$obj->set('Address', 'coucou');
	die('erreur' . __LINE__);
}catch(InvalidArgumentException $e)
{
	echo 'OK';
}

try{
	$obj->call('inexisting');
}catch(BadMethodCallException $e)
{
	echo 'unknown method threw exception';
}

$obj->setMethodNoutFoundHandler(function($method, $args, $result, $impl){
	echo 'unknown method exception handler executed';
	return 'hello';
	
});


echo $obj->call('inexisting');