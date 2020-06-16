<?php

namespace Epesi\Base\CommonData\Fields;

use atk4\data\Field_SQL;
use atk4\core\InitializerTrait;
use Epesi\Base\CommonData\Models\CommonData as CommonDataModel;

class CommonData extends Field_SQL
{
	use InitializerTrait {
		init as _init;
	}
	
	public $type = 'enum';
	
	public $path = '';

	public $serialize = [
	        [__CLASS__, 'serialize'],
	        [__CLASS__, 'unserialize'],
	];
	
	public $multiple = false;
	
	public $values;	

	/**
	 * Initialization.
	 */
	public function init(): void
	{
		$this->_init();
		
		if ($this->getDependency()) {
		    $this->ui['form'] = [
		            'AutoComplete', 
		            'model' => CommonDataModel::create(),
		            'dependency' => function($model, $data) {
		                $model->addCondition('parent', CommonDataModel::getId($this->getDependencyPath($data)));
        		    },
        		    'renderRowFunction' => function($field, $row) {
        		        return [
        		          'value' => $row['key'],
        		          'title' => $row['value'] ?: $row['key'],
        		        ];
        		     },
        		     'multiple' => $this->multiple
		    ];
		} else {
		    $this->values = CommonDataModel::getArray($this->path);
		}

		$this->ui = array_merge([
		        'table' => ['Multiformat', [$this, 'format']],
		        'form' => ['isMultiple' => $this->multiple]
		], $this->ui);
	}
	
	public function getDependency()
	{
	    $fields = (array) $this->path;
	    
	    $path = array_shift($fields);
	    
	    return $fields? compact('path', 'fields'): false;
	}
	
	public function getDependencyPath($data)
	{
	    if (! $dependency = $this->getDependency()) return false;
	    
	    return implode('/', array_merge([$dependency['path']], array_intersect_key($data, array_flip($dependency['fields']))));
	}
	
	public function getValues($data)
	{
	    $path = $this->getDependencyPath($data) ?: $this->path;
	    
	    return CommonDataModel::getArray($path);
	}
	
	public static function serialize($field, $value, $persistence)
	{
	    return $field->multiple? implode(',', (array) $value): $value;
	}
	
	public static function unserialize($field, $value, $persistence)
	{
	    return $field->multiple? explode(',', $value): $value;
	}

	public function format($row, $field)
	{	
	    $values = array_intersect_key($this->getValues($row->get()), array_flip((array) $row[$field]));
	    
	    return [[
	            'Template',
	            implode('<br>', $values),
	    ]];
	}
	
	public function normalize($value)
	{
	    return $this->multiple? parent::normalize($value): $value;
	}
}