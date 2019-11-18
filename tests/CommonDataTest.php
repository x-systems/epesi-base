<?php

use Orchestra\Testbench\TestCase;
use Epesi\Base\CommonData\Database\Models\CommonData;
use Epesi\Base\CommonData\Database\Models\CommonDataNotFound;

class CommonDataTest extends TestCase
{
	protected function getPackageProviders($app)
	{
		return [
				\Kalnoy\Nestedset\NestedSetServiceProvider::class,
		];
	}
	
	protected function setUp(): void
	{
		parent::setUp();
		
		$this->loadMigrationsFrom(__DIR__ . '../../src/CommonData/Database/Migrations');
	}
	
    public function testStoreRetrieve()
    {
    	// creating -  retrieving array
    	$arrayExpected = ['c' => 'cc', 'b' => 'bb', 'a' => 'aa', ];
    	
    	CommonData::newArray('abc/def', $arrayExpected);
        
        $arrayActual = CommonData::getArray('abc/def');
        
        $this->assertEquals($arrayExpected, $arrayActual, 'Problem retrieving commondata sorted by position!');
        
        // retrieving sorted array by key
        $arraySortKey = $arrayExpected;
        ksort($arraySortKey);
        
        $this->assertEquals($arraySortKey, CommonData::getArray('abc/def', 'key'), 'Problem retrieving commondata sorted by key!');
        
        // retrieving sorted array by value
        $arraySortValue = $arrayExpected;
        sort($arraySortValue);
        
        $this->assertEquals($arraySortKey, CommonData::getArray('abc/def', 'value'), 'Problem retrieving commondata sorted by value!');
        
        // retrieving array value
        $valueActual = CommonData::getValue('abc/def/a');
        
        $this->assertEquals($arrayExpected['a'], $valueActual, 'Problem retrieving commondata value!');
        
        // setting array values
        $arrayChanged = ['a' => 'aaa', 'b' => 'bbb', 'c' => 'ccc'];
        
        foreach ($arrayChanged as $key => $value) {
        	CommonData::setValue('abc/def/' . $key, $value);
        }
        
        $arrayActual = CommonData::getArray('abc/def');
        
        $this->assertEquals($arrayChanged, $arrayActual, 'Problem setting commondata value!');
        
        // deleting array node
        unset($arrayChanged['a']);
        
        CommonData::deleteArray('abc/def/a');
        
        $arrayActual = CommonData::getArray('abc/def');
        
        $this->assertEquals($arrayChanged, $arrayActual, 'Problem deleting commondata node!');
        
        // extending array
        $arrayExtension = ['a' => 'aaa'];
        
        $arrayChanged = array_merge($arrayChanged, $arrayExtension);
        
        CommonData::extendArray('abc/def', $arrayExtension);
        
        $arrayActual = CommonData::getArray('abc/def');
        
        $this->assertEquals($arrayChanged, $arrayActual, 'Problem extending commondata array!');
    }
    
    public function testNonExistingArrayException()
    {
    	$this->expectException(CommonDataNotFound::class);
    	
    	CommonData::getArray('abc/d/ef');
    }
    
    public function testNonExistingArraySilent()
    {
    	$this->assertEquals([], CommonData::getArray('abc/d/ef', 'position', true), 'Problem retrieving non-existent commondata array silently!');
    }
    
    public function testNonExistingValue()
    {
    	$this->assertFalse(CommonData::getValue('abc/d/ef'), 'Problem retrieving non-existent commondata value!');
    }
    
}
