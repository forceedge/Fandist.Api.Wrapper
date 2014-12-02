<?php // PHPUnitAssister Bootstrap file
 namespace PHPUnitAssister\Src\Core; class Debugger { public static function TombStone($date, $newMethod = null) { $callers = debug_backtrace(); $caller = $callers[1]['function']; echo "Depreciated method used: '{$caller}', on '{$date}'"; if($newMethod) { echo " - Use '{$newMethod}' instead"; } } public static function pre() { $arguments = func_get_args(); foreach($arguments as $argument) { print_r($argument); } } public static function prex() { $args = func_get_args(); foreach($args as $arg) { self::pre($arg); } exit(); } }
 namespace PHPUnitAssister\Src\Core; abstract class AssertionAssister extends \PHPUnit_Framework_TestCase{ protected $totest, $result, $lastMethod = [], $method, $reflectionMethod, $reflection; private function method($method, $params = false) { $this->reflectionMethod = $this->reflection->getMethod($method); if($params === false) { $this->totest = $this->result = $this->reflectionMethod->invoke($this->testObject); } else { $this->totest = $this->result = $this->reflectionMethod->invokeArgs($this->testObject, $params); } return $this; } public function getResult() { return $this->result; } public function getTestResult() { return $this->totest; } public function assertWith($params, $type, $expected = null) { Debugger::TombStone('14-11-14', 'with'); if($params === false) $this->method($this->reflectionMethod->getName()) ->assert($type, $expected); else $this->method($this->reflectionMethod->getName(), $params) ->assert($type, $expected); return $this; } public function setPropertyToTest($property) { $this->lastMethod[] = __METHOD__; if(! isset($this->totest->$property)) $this->throwException (new \Exception("Expected property: $property in ". get_class($this->totest))); $this->totest = $this->totest->$property; return $this; } public function callMethodToTest($method, $args = array()) { $this->lastMethod[] = __METHOD__; if(! is_object($this->totest)) { throw new \Exception("Cannot call method on a non object"); } if(! method_exists($this->totest, $method)) { throw new \Exception("object method '{$method}' not found in class definition of '". get_class($this->totest)). "'"; } $this->totest = call_user_method_array($method, $this->totest, $args); return $this; } public function setTestResult($testable) { $this->lastMethod[] = __METHOD__; $this->totest = $testable; return $this; } public function repeat() { $args = func_get_args(); $method = end($this->lastMethod); if(empty($method)) $this->throwException (new \Exception("cannot repeat empty method given in ". get_class($this->totest))); call_user_method_array($method, $this, $args); return $this; } public function setIndexToTest($index) { $this->lastMethod[] = __METHOD__; if(! isset($this->totest[$index])) $this->throwException (new \Exception("Expected index: $index in ". print_r($this->totest, true))); $this->totest = $this->totest[$index]; return $this; } public function resetResultToTest() { $this->totest = $this->result; return $this; } private function isAssertionFunc($type, $expected = null) { $asserted = false; switch (strtolower($type)) { case 'regexp': { $this->assertRegExp($expected, $this->totest, $this->setMessage('match regex '.$expected, $this->totest)); $asserted = true; break; } case 'arrayhaskey': { $this->assertArrayHasKey($expected, $this->totest, $this->setMessage('array has key '.$expected, $this->totest)); $asserted = true; break; } case 'isarray': { $this->assertTrue(is_array($this->totest), $this->setMessage('should be an array', $this->totest)); $asserted = true; break; } case 'isjson': { json_decode($this->totest); $this->assertTrue(json_last_error() == JSON_ERROR_NONE, $this->setMessage('should be a json', $this->totest)); } case 'isobject': { if($expected) { if(is_object($expected)) $expected = get_class ($expected); $this->assertTrue(get_class($this->totest) == trim($expected,'\\'), $this->setMessage("should be an object of class type '$expected'", $this->totest)); } else $this->assertTrue(is_object($this->totest), $this->setMessage('should be an object', $this->totest)); $asserted = true; break; } case 'contains': { $this->assertTrue((strpos($this->totest, $expected) !== false), $this->setMessage("expected '$expected' to be in result", $this->totest)); $asserted = true; break; } } if($asserted) return true; return false; } private function resolveStringBasedAssertion($type, $expected = null) { $assertMethod = $this->getAssertMethod($type); if(is_object($expected)) { $this->$assertMethod($this->totest instanceof $expected, $this->setMessage('instance of '. get_class($expected), $this->totest)); } else if(! is_array($expected) and strpos($expected, '==')) { list($assertType, $val) = explode('==', $expected); switch($assertType) { case '!': $this->$assertMethod($this->totest !== $val, $this->setMessage('not to be '.$val, $this->totest)); break; case '[]': $this->$assertMethod(count($this->totest) == $val, $this->setMessage('array elements count be equal to '.$val, count($this->totest))); break; case '->': $this->$assertMethod($this->totest instanceof $val, $this->setMessage('object instance of '.$val, $this->totest)); break; default: $this->resolveUnusualAssertion($assertMethod, $assertType, $val); break; } } else { if($expected == 'null') $this->$assertMethod($this->totest == null, $this->setMessage($expected, $this->totest)); else $this->$assertMethod($this->totest == $expected, $this->setMessage($expected, $this->totest)); } } private function resolveUnusualAssertion($assertMethod, $type, $val) { if(preg_match("/^\[.+\]$/", $type)) { $index = trim(trim($type, '['), ']'); if($val == 'null') $this->$assertMethod($this->totest[$index] == null, $this->setMessage("Index '$index' not equal to null in ".print_r($this->totest, true), $this->totest)); else $this->$assertMethod($this->totest[$index] == $val, $this->setMessage("Index '$index' not equal to '$val' in ".print_r($this->totest, true), $this->totest)); } else if(preg_match("/^->.+$/", $type)) { $property = str_replace('->', '', $type); if($val == 'null') $this->$assertMethod($this->totest->$property == null, $this->setMessage("Property '$property' not equal to null in ".get_class($this->totest), $this->totest)); else $this->$assertMethod($this->totest->$property == $val, $this->setMessage("Property '$property' not equal to '$val' in ".get_class($this->totest), $this->totest)); } } private function getAssertMethod($type) { $methodType = ucfirst($type); return "assert{$methodType}"; } public function assert($type, $expected = null) { $assertMethod = $this->getAssertMethod($type); $asserted = $this->isAssertionFunc($type, $expected); if(! $asserted) { if($expected || is_array($expected)) { $this->resolveStringBasedAssertion($type, $expected); } else { $this->$assertMethod($this->totest, $this->setMessage($type, $this->totest)); } } return $this; } public function assertSelfInstance($result) { $obj = $this->getTestObject(); $this->assertTrue($result instanceof $obj, $this->setMessage('instance of '. get_class($obj), $result)); return $this; } private function setMessage($expected, $response) { $formattedResponse = $response; if(is_object($formattedResponse)) { $formattedResponse = 'instance of '.get_class($formattedResponse); } else if(is_array($formattedResponse)) { $formattedResponse = print_r($formattedResponse, true); } $formattedExpected = $expected; if(is_object($formattedExpected) || is_array($formattedExpected)) { $formattedExpected = print_r($formattedExpected, true); } return "\n\nExpected (++) \nActual (--) \n\n@++ $formattedExpected\n@-- $formattedResponse\n"; } public function tm($method) { $this->method = $method; return $this; } public function with() { $args = func_get_args(); $this->method($this->method, $args); return $this; } public function setExpectedExc($exception) { $this->setExpectedException($exception); return $this; } }
 namespace PHPUnitAssister\Src\Core; class invoker implements \PHPUnit_Framework_MockObject_Invocation {} abstract class Mocker extends AssertionAssister { public $previousMock; public $mockObject; public $mockObjects = array(); private $mockProviders = array(); public function setMock($object) { $this->setmo($object); return $this; } public function mock($method, $returnValue = null) { $this->mm($method, array('will' => $this->returnValue($returnValue))); return $this; } public function mockMethods(array $methods, array $options = array()) { $this->mmx($methods, $options); return $this; } public function mockMethod($method, array $params = array()) { $this->mm($method, $params); return $this; } public function getMockedObject() { return $this->getmo(); } public function getMockedObjects() { return $this->getmos(); } public function setBaseMock() { $this->setbm(); return $this; } public function setbm() { $this->mockObjects[] = $this->mockObjects[0]; return $this; } public function then($method, array $options = array()) { return $this->setMockObject($this->previousMock) ->mm($method, $options); } public function mm($method, array $options = array()) { $this->mockObject = end($this->mockObjects); if(is_array($method) and count($method) > 0) { foreach($method as $met) { $this->mmSingle($met, $options); } } else { $this->mmSingle($method, $options); } return $this; } private function mmSingle($method, array $options = array()) { if(! is_object($this->mockObject)) { throw new \Exception('Unable to mock method \''.$method.'\', expected mock object from class '. get_called_class() . ', got: '.print_r($this->mockObject, true)); } if(! method_exists($this->mockObject, $method)) { throw new \Exception("Method '{$method}' does not exist for mock object " . get_class($this->mockObject)); } $expects = isset($options['expects']) ? $options['expects'] : $this->any(); $with = isset($options['with']) ? $options['with'] : ''; $withArgs = isset($options['withArgs']) ? $options['withArgs'] : ''; $will = isset($options['will']) ? $options['will'] : $this->returnSelf(); $mocked = $this->mockObject->expects($expects) ->method($method); $this ->performWith($mocked, $with) ->performWithArgs($mocked, $withArgs) ->performWill($mocked, $will); return $this; } private function performWithArgs($mocked, $withArgs = null) { if($withArgs) { if(! is_array($withArgs)) { throw new \Exception('withArgs requires either an array or null value as input,  '.gettype($withArgs)); } call_user_method_array('with', $mocked, $withArgs); } return $this; } private function performWith($mocked, $with = null) { if($with) { if(is_array($with) and isset($with['withArgs'])) { call_user_method_array('with', $mocked, $with['withArgs']); } else { $mocked->with($with); } } return $this; } private function performWill($mocked, $will = null) { if(is_object($will)) { if(get_class($will) === 'PHPUnit_Framework_MockObject_Stub_Return') $this->previousMock = $will->invoke(new invoker()); $mocked->will($will); } else throw new \Exception('Will clause expects an object, ' . gettype($will) . ' provided'); return $this; } public function getmo() { return $this->mockObjects[0]; } public function getmos() { return $this->mockObjects; } public function setmo($mockedObject) { unset($this->mockObjects); unset($this->mockObject); unset($this->previousMock); $this->mockObjects = []; $this->setMockObject($mockedObject); return $this; } private function setMockObject($mockedObject) { $this->mockObjects[] = $mockedObject; return $this; } public function getMockProvider($mockProviderClass = 'MockProvider') { if(isset($this->mockProviders[$mockProviderClass])) { return $this->mockProviders[$mockProviderClass]; } $qualifiedClass = "\\PHPUnitAssister\\Src\\Extensions\\$mockProviderClass"; \PHPUnitAssister\Loader::LoadExtendedFileByClass($mockProviderClass); $this->mockProviders[$mockProviderClass] = new $qualifiedClass; return $this->mockProviders[$mockProviderClass]; } public function mmx(array $methods, array $options = array()) { if($options) { foreach($methods as $method) { $this->mm($method, $options); } } else { foreach($methods as $method => $value) { if(is_int($method)) { $this->mm($value); } else { $this->mm($method, array('will' => $this->returnValue($value))); } } } return $this; } }
 namespace PHPUnitAssister\Src\Core; abstract class TestObjectHandler extends Mocker{ protected $testObject; protected $args; public function setTestObject($class, array $args = array()) { $this->setReflection($class); $this->args = $args; if($this->args) { $this->testObject = $this->reflection->newInstanceArgs($this->args); } else { $this->testObject = $this->reflection->newInstance(); } return $this; } public function setTestObjectProperty($property, $value) { $this->setReflection($this->testObject); $reflectionProperty = $this->reflection->getProperty($property); $reflectionProperty->setAccessible(true); $reflectionProperty->setValue($this->testObject, $value); return $this; } public function resetTestObject(array $args) { $this->setReflection($this->testObject); $this->args = $args; $this->testObject = $this->reflection->newInstanceArgs($this->args); return $this; } public function resetTestObjectArgument($index, $argument) { $this->args[$index] = null; $this->args[$index] = $argument; $this->resetTestObject($this->args); return $this; } private function setReflection($class) { $this->reflection = new \ReflectionClass($class); return $this; } public function getTestObject() { return $this->testObject; } }