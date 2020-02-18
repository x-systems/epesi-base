<?php

namespace Epesi\Base\CommonData\Models;

use Illuminate\Database\Eloquent\Collection;
use atk4\data\Model;
use Epesi\Core\Data\HasEpesiConnection;

class CommonDataNotFound extends \Exception {}

class CommonData extends Model {
    use HasEpesiConnection;
    
    public $table = 'commondata';
    
    public $caption = 'Common Data';
    
    public $title_field = 'value';
    
    protected static $cache = [
    		'id' => [],
    		'value' => [],
    		'array' => []
    ];
    
    function init() {
        parent::init();
        
        $this->addFields([
                'key' => ['caption' => __('Key')],
                'value' => ['caption' => __('Value')],
                'position' => ['type' => 'integer', 'caption' => __('Position')],
                'readonly' => ['type' => 'boolean']
        ]);
        
        $this->hasOne('parent', [self::class, 'our_field' => 'parent']);
        
        $this->getAction('edit')->ui['execButton'] = ['Button', __('Save'), 'class' => ['primary']];
        
        $this->addHook('beforeInsert', function($node, & $data) {
            $data['position'] = $node->action('fx', ['max', 'position'])->getOne() + 1;
            
            $data['readonly'] = $data['readonly'] ?? 0;
        });
    }
    
    public static function getId($path, $clearCache = false)
    {
    	$id = null;
    	foreach(explode('/', trim($path,'/')) as $nodeKey) {
    	    $parentId = $id;
    	    
    		if ($nodeKey === '') continue; //ignore empty paths
    		
    		if ($clearCache || empty(self::$cache['id'][$parentId][$nodeKey])) {
    		    $node = self::siblings($parentId)->addCondition('key', $nodeKey)->tryLoadAny();
    		    
    			if (! $node->loaded()) return false;

    			self::$cache['id'][$parentId][$nodeKey] = $node->id;
    		}
    		
    		$id = self::$cache['id'][$parentId][$nodeKey];
    	}
    	
    	return $id;
    }
   
    public static function newId($path, $readonly = false)
    {
    	if (! $path = trim($path,'/')) return false;

    	$id = null;
    	foreach(explode('/', $path) as $nodeKey) {
    		if ($nodeKey === '') continue;
    		
    		$parentId = $id;
    		
    		$node = self::siblings($parentId)->addCondition('key', $nodeKey)->tryLoadAny();

    		if ($node->loaded()) {
    		    $id = $node->id;
    		}
    		else {
    		    $id = $node->insert([
    		            'parent' => $parentId,
    		            'key' => $nodeKey,
    		            'readonly' => $readonly
    		    ]);
    		}
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

    	self::create()->tryLoad($id)->save(compact('value', 'readonly'));
    	
    	self::clearCache();
    	
    	return true;
    }
    
    public static function clearCache()
    {
    	self::$cache = array_fill_keys(array_keys(self::$cache), []);
    }
    
    public static function getValue($path, $translate = false)
    {
    	$key = md5(serialize($path));
    	
    	if (! isset(self::$cache['value'][$key])) {
    		if(! $id = self::getId($path)) return false;

    		self::$cache['value'][$key] = self::create()->tryLoad($id)->get('value');
	    }
	    
	    return $translate? __(self::$cache['value'][$key]): self::$cache['value'][$key];
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
    				
    		self::create()->delete($id);
    	}
    			
    	if(! $id = self::newId($path, $readonly)) return false;
    			
    	if ($overwrite) {
    	    self::create()->tryLoad($id)->save(compact('readonly'));
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
    	
    	self::create()->delete($id);
    	
    	self::clearCache();
    }
    
    public static function siblings($parent = null)
    {
        $parentId = is_numeric($parent)? $parent: self::getId($parent);

        $model = self::create();
        
        //@TODO: remove below when adding null condition fixed
        return $parentId? $model->addCondition('parent', $parentId): $model->addCondition($model->expr('parent is NULL'));
    }
        
    public static function ancestors($node)
    {
        $node = is_numeric($node)? self::create()->tryLoad($node): $node;

        if (! $node['parent']) return [];
        
        return array_filter(array_merge([$node['parent']], self::ancestors($node['parent'])));
    }
    
    public static function ancestorsAndSelf($node)
    {
        $node = is_numeric($node)? self::create()->tryLoad($node): $node;
        
        return array_filter(array_merge([$node['id']], self::ancestors($node)));
    }
    
    /**
     * Returns common data collection.
     *
     * @param $path string
     * @return Collection
     */
    public static function getCollection($path, $silent = false)
    {
    	if(isset(self::$cache['array'][$path])) {
    		return self::$cache['array'][$path];
    	}
    	
    	if (! $id = self::getId($path)) {
    		if ($silent) return collect();
    		
    		throw new CommonDataNotFound('Invalid CommonData::getArray() request: ' . $path);
    	}
    	
    	return self::$cache['array'][$path] = collect(self::siblings($id)->export());
    }
    
    protected static function validateArrayKeys($array)
    {
    	foreach($array as $key => $value) {
    		if (strpos($key, '/') === false) continue;
    		
    		\Exception('Invalid common data key: '. $key);
    	}
    }
}