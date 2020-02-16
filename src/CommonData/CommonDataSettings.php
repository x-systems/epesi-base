<?php

namespace Epesi\Base\CommonData;

use Epesi\Core\System\Modules\ModuleView;
use Illuminate\Support\Facades\Auth;
use Epesi\Core\Layout\Seeds\ActionBar;
use atk4\ui\jsExpression;
use atk4\core\SessionTrait;

class CommonDataSettings extends ModuleView
{
    use SessionTrait;
    
	protected $label = 'Common Data';
	
	protected $ancestors;

	protected $grid;
	
	public static function access()
	{
		return Auth::user()->can('modify system settings');
	}
	
	public function body()
	{
		ActionBar::addButton('back')->link(url('view/system'));
		
		$this->setAncestors();
		
		$this->setLocation();
		
		$this->displayGrid();
	}
	
	public function setAncestors()
	{
	    $parent = $this->stickyGet('parent');
	    
	    if (isset($parent)) {
	        $this->memorize('parent', $parent?: null);
	    }

	    $this->ancestors = Models\CommonData::ancestorsAndSelf($this->recall('parent'));
	}
	
	public function setLocation()
	{
	    $location = [['label' => $this->label, 'link' => '?parent=0']];	    
		foreach (Models\CommonData::create()->withID($this->ancestors) as $node) {
		    $location[] = [
		            'label' => $node['value'] ?: $node['key'], 
		            'link' => '?parent=' . $node['id']
		    ];
		}

		$this->label = null;
		
		$this->location($location);
		
		return $this;
	}
	
	public function displayGrid()
	{		
		$this->grid = $this->add([
				'CRUD',
		        'model' =>  $this->nodes()->setOrder('position'),
				'editFields' => ['key', 'value'],
				'displayFields' => ['key', 'value', 'readonly'],
		        'notifyDefault' => ['jsToast', 'settings' => ['message' => __('Data is saved!'), 'class' => 'success']],
				'paginator' => false,
		]);

		$this->grid->addActionButton(['icon' => 'level down', 'attr' => ['title' => __('Drilldown')]], new jsExpression(
		        'document.location=\'?parent=\'+[]',
		        [$this->grid->jsRow()->data('id')]
		));

		$this->grid->addDragHandler()->onReorder(function ($order) {
			$result = true;
			foreach ($this->nodes() as $node) {
				$result &= $node->save(['position' => array_search($node->id, $order)]);
			}
			
			$notifier = $result? $this->notifySuccess(__('Items reordered!')): $this->notifyError(__('Error saving order!'));
			
			return $this->grid->jsSave($notifier);
		});

		if ($this->ancestors && $this->grid->menu) {
		    $this->grid->menu->addItem([__('Level Up'), 'icon' => 'level up'], '?parent=' . $this->parent(1));
		}
		
		return $this;
	}
	
	public function parent($level = 0)
	{
	    return $this->ancestors[$level] ?? 0;
	}
	
	public function nodes()
	{
	    $nodes = Models\CommonData::create();
	    
	    //@TODO: remove below when adding null condition fixed
	    if ($parent = $this->parent()) {
	        $nodes->addCondition('parent', $parent);
	    }
	    else {
	        $nodes->addCondition($nodes->expr('parent is NULL'));
	    }

	    $nodes->addHook('beforeInsert', function($node, & $data) {
	        $data['readonly'] = false;
	        $data['parent'] = $this->parent();
	        $data['position'] = $node->action('fx', ['max', 'position'])->getOne() + 1;
	    });
	        
	    $nodes->getAction('edit')->enabled = function($row) {
	        return !$row['readonly'];
	    };
	    
	    return $nodes;
	}
}
