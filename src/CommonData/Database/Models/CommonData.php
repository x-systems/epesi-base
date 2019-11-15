<?php

namespace Epesi\Base\CommonData\Database\Models;

use Kalnoy\Nestedset\NodeTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

class CommonData extends Model {
    use NodeTrait;
    
    protected $table = 'commondata';
    public $timestamps = false;
    protected static $unguarded = true;
    
    public static function getId($path, $clearCache = false)
    {
    	static $cache;

    	$parentId = null;
    	foreach(explode('/', trim($path,'/')) as $nodeKey) {
    		if ($nodeKey === '') continue; //ignore empty paths
    		
    		if (empty($cache[$parentId][$nodeKey])) {
    			if (! $node = self::where('parent_id', $parentId)->where('key', $nodeKey)->first()) {
    				return false;
    			}

    			$cache[$parentId][$nodeKey] = $node->id;
    		}
    		
    		$parentId = $id = $cache[$parentId][$nodeKey];
    	}
    	
    	return $id;
    }
   
    public static function newId($path, $readonly = false)
    {
    	if (! $path = trim($path,'/')) return false;

    	$id = $parentId = null;
    	foreach(explode('/', $path) as $nodeKey) {
    		if ($nodeKey === '') continue;

    		if (! $node = self::where('parent_id', $parentId)->where('key', $nodeKey)->first()) {
    			$node = self::create([
    					'parent_id' => $parentId,
    					'key' => $nodeKey,
    					'readonly' => $readonly,
    					'position' => self::where('parent_id', $parentId)->count()
    			], $parentId? self::find($parentId): null);
    		}
    		
    		$parentId = $id = $node->id;
    	}
    	
    	return $id;
    }
    
    public static function setValue($path, $value, $overwrite = true, $readonly = false)
    {
    	if (! $id = self::getId($path)) {
    		if (! $id = self::newId($path, $readonly)) return false;
    	} else {
    		if (! $overwrite) return false;
    	}
    	
    	self::find($id)->update(compact('value', 'readonly'));
    	
    	return true;
    }
    
    public static function getValue($path, $translate = false)
    {
    	static $cache;
    	
    	$key = md5(serialize([$path, $translate]));
    	
    	if (! isset($cache[$key])) {
    		if(! $id = self::getId($path)) return false;

    		$ret = self::find($id)->value;

	    	$cache[$key] = $translate? __($ret): $ret;
	    }
	    
	    return $cache[$key];
    }
    
    
    /**
     * Creates new array for common use.
     *
     * @param $path string
     * @param $array array initialization value
     * @param $overwrite bool whether method should overwrite if array already exists, otherwise the data will be appended
     * @param $readonly bool do not allow user to change this array from GUI
     */
    public static function newArray($path, $array, $overwrite = false, $readonly = false)
    {
    	self::validateArrayKeys($array);
    		
    	$path = trim($path, '/');
    	
		if ($id = self::getId($path)) {
    		if (! $overwrite) {
    			self::extendArray($path, $array);
    			return true;
    		}
    				
    		self::find($id)->delete();
    	}
    			
    	if(! $id = self::newId($path, $readonly)) return false;
    			
    	if ($overwrite) {
    		self::find($id)->update(compact('readonly'));
    	}
    			
    	foreach ($array as $key => $value) {
    		self::setValue($path . '/' . $key, $value, true, $readonly);
    	}
    			
    	return true;
    }

    /**
     * Extends common data array.
     *
     * @param $path string
     * @param $array array values to insert
     * @param $overwrite bool whether method should overwrite data if array key already exists, otherwise the data will be preserved
     */
    public static function extendArray($path, $array, $overwrite=false, $readonly=false)
    {
    	self::validateArrayKeys($array);
    	
    	$path = trim($path, '/');
    			
    	if (! self::getId($path)){
    		return self::newArray($path, $array, $overwrite, $readonly);
    	}

    	foreach ($array as $key => $value) {
    		self::setValue($path . '/' . $key, $value, $overwrite, $readonly);
    	}
    }
        
    /**
     * Returns common data array.
     *
     * @param string array name
     * @return mixed returns an array if such array exists, false otherwise
     */
    public static function getArray($path, $sortColumn = 'position', $silent = false)
    {
    	return self::getCollection($path, $silent)->sortBy($sortColumn)->pluck('value', 'key')->all();
    }

    /**
     * Removes common data array or entry.
     *
     * @param $path string
     * @return true on success, false otherwise
     */
    public static function deleteArray($path){
    	if (! $id = self::getId($path, true)) return false;
    	
    	self::find($id)->delete();
    }

    /**
     * Returns common data collection.
     *
     * @param $path string
     * @return Collection
     */
    public static function getCollection($path, $silent = false)
    {
    	static $cache;

    	if(isset($cache[$path])) {
    		return $cache[$path];
    	}
    	
    	if (! $id = self::getId($path)) {
    		if ($silent) return collection();
    		
    		new \Exception('Invalid CommonData::getArray() request: ' . $path);
    	}
    	
    	return $cache[$path] = self::where('parent_id', $id)->get();
    }
    
    protected static function validateArrayKeys($array)
    {
    	foreach($array as $key => $value) {
    		if (strpos($key, '/') === false) continue;
    		
    		\Exception('Invalid common data key: '. $key);
    	}
    }
}