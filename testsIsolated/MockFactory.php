<?php
// Based on
// https://github.com/hafriedlander/phockito/blob/master/Phockito.php#L170

if ( !defined( 'ABSPATH' ) ) exit;

class MockFactory
{
    public function buildMock( $class )
    {
        $reflection = new \ReflectionClass( $class );
        $mockedShortName = $reflection->getShortName();
        $mockShortName = "Mock$mockedShortName";
        $mockClass = "\\$mockShortName";
        if ( ! class_exists($mockClass) ) {
            $this->declareMockClass( $reflection, $mockShortName, $mockedShortName );
        }
        return new $mockClass;
    }
    private function declareMockClass( $reflection, $mockShortName, $mockedShortName )
    {
        $php = [];
        $mockedNamespace = $reflection->getNamespaceName();
        $extends = $reflection->isInterface() ? 'implements' : 'extends';
        $php[] = <<<EOT
class $mockShortName $extends $mockedNamespace\\$mockedShortName {
	public function __construct() {}
	public function setReturnValue( \$method, \$returnValue ) {
		\$field = \$method . "ReturnValue";
		\$this->\$field = \$returnValue;
	}
	
	public function setReturnValues( \$method, \$returnValues ) {
		\$field = \$method . "ReturnValues";
		\$this->\$field = \$returnValues;
	}
	public function getCalls ( \$method ) {
		\$callsProperty = \$method . "Calls";
	
		return \$this->\$callsProperty;
	}
EOT;
        $php = $this->addMethodPhp( $reflection, $php );
        $php[] = '}';
        $toEval = implode( "\n\n", $php );
        eval( $toEval );
    }
    private function addMethodPhp( $reflection, $php )
    {
        foreach ( $reflection->getMethods() as $method ) {
            $methodName = $method->name;
            if ( $methodName === "__construct" ) continue;
            $params = [];
            foreach ( $method->getParameters() as $i => $parameter ) {
                if ( $parameter->isArray() ) $type = 'array ';
                else if ( $parameterClass = $parameter->getClass() ) $type = '\\' . $parameterClass->getName() . ' ';
                else $type = '';
                $default = ( $parameter->isOptional() ) ? "= null" : "";
                $params[] = "$type \${$parameter->getName()} $default";
            }
            $paramString = implode( ',', $params );
            $valueField = $methodName . "ReturnValue";
            $valuesField = $methodName . "ReturnValues";
            $php[] = <<<EOT
	private \${$methodName}Calls = [];
	
	public function $methodName($paramString) {
		\$this->{$methodName}Calls[] = func_get_args();
		 
		if ( ! empty(\$this->$valuesField) ) {
			return array_shift(\$this->$valuesField);
		}
		
		if ( isset( \$this->$valueField ) ) {
			return \$this->$valueField;
		}
	}
EOT;
        }
        //var_dump($php);
        return $php;
    }
}