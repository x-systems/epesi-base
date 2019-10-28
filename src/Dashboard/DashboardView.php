<?php

namespace Epesi\Base\Dashboard;

use atk4\ui\jsExpression;
use atk4\ui\jsFunction;
use atk4\ui\FormField\Input;
use Epesi\Core\UI\Seeds\Form;
use Epesi\Core\UI\Seeds\ActionBar;
use Epesi\Core\Integration\Module\ModuleView;
use Epesi\Base\Dashboard\UI\Seeds\Applet;
use Epesi\Base\Dashboard\Database\Models\Dashboard;
use Epesi\Base\Dashboard\Integration\Joints\AppletJoint;
use Illuminate\Support\Facades\Auth;

class DashboardView extends ModuleView
{
	protected $label = 'Dashboard';
	
	protected $dashboard;
	protected $columns;
	protected $admin = false;
	protected $locked = false;	
	
	public function body()
	{
		if (! $this->isSingleDashboard()) {
			$this->location($this->dashboard()->name);
		}
		
		// initiate the dashboard first
		$this->dashboard();
		
		$this->addMenu();
		
		$this->showDashboard();
	}
	
	public function showDashboard()
	{
		$dashboard = $this->dashboard();
		
		$applets = $dashboard->applets()->orderBy('column')->orderBy('row')->get();

		$columns = $this->add(['Columns', 'id' => 'dashboard', 'ui' => 'three stackable grid'  . ($this->isLocked()? ' locked': '')]);
		
		foreach ([1, 2, 3] as $columnId) {
			/** @scrutinizer ignore-call */
			$col = $columns->addColumn([
					'',
					'ui' => 'sortable',
					'attr' => [
							'dashboard-id' => $dashboard->id,
							'column-id' => $columnId
					]
			]);
			
			foreach ($applets->where('column', $columnId) as $applet ) {
				$col->add([
						new Applet(),
						'appletId' => $applet->id,
						'jointClass' => $applet->class,
						'options' => $applet->options,
						'locked' => $this->isLocked()
				]);
			}
		}
		
		if (! $this->isLocked()) {
			$columns->js(true)->find('.sortable')->sortable([
					'cursor' => 'move',
					'handle' => '.panel-sortable-handle',
					'connectWith' => '.sortable',
					'items' => '.applet',
			]);
			
			$columns->js(true)->find('.column.sortable')->on('sortupdate', new jsFunction([
					$columns->add('jsCallback')->set([$this, 'saveColumn'], [
							new jsExpression('
							{
								column: $(this).attr("column-id"),
								applets: $(this).sortable( "toArray", { attribute: "applet-id" })
							}')
					])
			]));
			
			$columns->js(true)->find('.applet-close')->click(new jsFunction(['e'], [new jsExpression('if (confirm("' .  __('Delete this applet?') . '")) {$(e.target).closest(".applet").fadeOut(400, function(){var col = $(this).closest(".column.sortable");this.remove();col.trigger("sortupdate");})}')]));
		}
		
		$this->columns = $columns;
		
		$this->requireCSS();
	}
		
	public function editDashboard()
	{
		$dashboard = $this->dashboard();
		
		$this->location([$dashboard->name, __('Find Applets')]);
		
		$this->showDashboard();
		
		ActionBar::addButton('back');
		
		$adminColumn = $this->columns->addColumn();
		
		$search = $adminColumn->add(new Input([
				'placeholder' => __('Search applets'),
				'icon' => 'search'
		]))->setStyle(['width' => '100%']);
		
		$search->js(true)->on('keyup', new jsFunction(['e'], [
				new jsExpression('
					var str = $(e.target).val().toLowerCase();
						
    				$("#dashboard_applets_new").children(".applet").each(function(i, nodeObj) {
			        	var node = $(nodeObj);
						
			        	node.toggle(node.attr("searchkey").indexOf(str) != -1);
			    });
			')
		]));
		
		$col = $adminColumn->add([
				'View',
				'id' => 'dashboard_applets_new',
				'ui' => 'admin sortable',
				'attr' => [
						'dashboard-id' => $dashboard->id,
						'column-id' => 'admin'
				]
		]);
		
		foreach ( AppletJoint::collect() as $applet ) {
			$col->add([
					new Applet(),
					'appletId' => 'new_' . str_ireplace('\\', '-', get_class($applet)),
					'jointClass' => $applet,
					'admin' => 1,
			]);
		}
	}
		
	public function addMenu()
	{
		if ($this->isLocked()) return;
		
		$dashboardId = $this->dashboard()->id;
		
		$dashboardMenu = $this->app->layout->menuRight->addMenu([
				'',
				'icon' => 'ellipsis vertical',
				'attr' => [
						'title' => __('Find dashboard applets')
				]
		]);
		
		$dashboardMenu->js(true)->find('i.dropdown.icon')->remove();
		
		// ***** edit ***** //
		$dashboardMenu->addItem([__('Add applets'), 'icon' => 'edit'])->link($this->selfLink('editDashboard', ['dashboard' => $dashboardId]));
		
		// ***** rename ***** //
		$modal = $this->add(['Modal', 'title' => __('Rename Dashboard')])->set(\Closure::fromCallable([$this, 'renameDashboard']));
		
		$dashboardMenu->addItem([__('Rename dashboard'), 'icon' => 'i cursor'])->on('click', $modal->show());
		
		// there is only one admin default dashboard
		if ($this->admin) return;
		
		// ***** add ***** //		
		$modal = $this->add(['Modal', 'title' => __('Add Dashboard')])->set(\Closure::fromCallable([$this, 'addDashboard']));
		
		$dashboardMenu->addItem([__('Add dashboard'), 'icon' => 'add'])->on('click', $modal->show());
		
		if ($this->isSingleDashboard()) return;
		
		// ***** reorder ***** //
		$modal = $this->add(['Modal', 'title' => __('Reorder Dashboards')])->set(\Closure::fromCallable([$this, 'reorderDashboards']));
		
		$dashboardMenu->addItem([__('Reorder dashboards'), 'icon' => 'sort'])->on('click', $modal->show());
		
		// ***** delete ***** //
		$deleteButton = $dashboardMenu->addItem([__('Delete dashboard'), 'icon' => 'trash', 'attr' => ['title' => __('Delete current dashboard')]]);
		
		$deleteDashboard = $deleteButton->add('jsCallback')->set([$this, 'deleteDashboard'], [$dashboardId]);
		
		$deleteButton->on('click', [
				new jsExpression('if (! confirm([])) return;', [__('Delete current dashboard?')]),
				$deleteDashboard
		]);
	}
	
	public function addDashboard($view)
	{
		$form = $view->add(new Form(['buttonSave' => ['Button', __('Create Dashboard'), 'primary']]));
		
		$existing = Dashboard::whereIn('user_id', [0, $this->userId()])->pluck('name', 'id');
		
		$form->addField('name', __('Name'));
		$form->addField('base', [
				'DropDown',
				'caption' => __('Copy applets from'),
				'values' => $existing
		]);
		
		$form->layout->addButton(['Button', __('Cancel')])->on('click', $view->owner->hide());
		
		$form->onSubmit(function($form) use ($existing) {
			$values = $form->getValues();
			
			$dashboard = Dashboard::create([
					'name' => $values['name'],
					'user_id' => $this->userId(),
					'position' => count($existing)
			]);
			
			if ($values['base']) {
				foreach (Dashboard::find($values['base'])->applets()->get() as $baseApplet) {
					$applet = $baseApplet->replicate();
					
					$applet->dashboard_id = $dashboard->id;
					
					$applet->save();
				}
			}
			
			return [
					$form->notify(__('Dashboard created, redirecting ...')),
					new jsExpression('window.setTimeout(function() {window.location.replace([])}, 1200)', [self::selfLink('body', ['dashboard' => $dashboard->id])])
			];
		});
	}
	
	public function renameDashboard($view)
	{
		$form = $view->add(new Form(['buttonSave' => ['Button', __('Save'), 'primary']]));

		$form->addField('name', __('New Name'))->set($this->dashboard()->name);
		
		$form->layout->addButton(['Button', __('Cancel')])->on('click', $view->owner->hide());
		
		$form->onSubmit(function($form) {
			$values = $form->getValues();
			
			$dashboard = $this->dashboard();
			
			$dashboard->name = $values['name'];
			
			$dashboard->save();
			
			return [
					$form->notify(__('Dashboard renamed, reloading ...')),
					new jsExpression('window.setTimeout(function() {window.location.replace([])}, 1200)', [self::selfLink('body', ['dashboard' => $dashboard->id])])
			];
		});
	}
	
	public function deleteDashboard($jsCallback, $dashboardId) 
	{
		$dashboard = Dashboard::find($dashboardId);

		return $dashboard->delete()? [
				$this->notify(__('Dashboard ":name" deleted, redirecting ...', ['name' => $dashboard->name])),
				new jsExpression('window.setTimeout(function() {window.location.replace([])}, 1200)', [self::selfLink()])
		]: $this->notifyError(__('Error deleting dashboard'));
	}
	
	public function reorderDashboards($view)
	{
		$dashboards = $this->userDashboards()->orderBy('position')->get();
		
		$rows = [];
		foreach ($dashboards as $dashboard) {
			$rows[] = [
					'id' => $dashboard->id,
					__('Dashboard') => $dashboard->name,
					str_ireplace(' ', '_', __('Old Position')) => count($rows) + 1,
			];
		}
		
		$grid = $view->add(['Grid', 'paginator' => false, 'menu' => false]);
		$grid->setModel(new \atk4\data\Model(new \atk4\data\Persistence_Static($rows)));
		
		$grid->addDragHandler()->onReorder(function ($order) use ($dashboards) {
			$result = true;
			foreach ($dashboards as $dashboard) {
				$dashboard->position = array_search($dashboard->id, $order);
				
				$result &= $dashboard->save();
			}
			
			return $result? $this->notify(__('Dashboards reordered!')): $this->notifyError(__('Error saving order!'));
		});
			
		$view->add(['View', 'ui' => 'buttons'])->add(['Button', __('Done'), 'primary'])->on('click', new jsExpression('location.reload()'));
	}
	
	public function saveColumn($jsCallback, $columnHash)
	{
		$applets = $columnHash['applets']?? [];
		
		if ($new = preg_grep('/^new_/', $applets)) {
			$new = reset($new);
			
			$row = array_search($new, $applets);

			$applet = $this->dashboard()->applets()->create([
					'class' => str_ireplace('-', '\\', preg_replace('/^new_/', '', $new)),
					'column' => $columnHash['column'],
					'row' => $row
			]);
			
			$applets[$row] = $applet->id;
		}

		foreach ($this->dashboard()->applets()->whereIn('id', $applets)->get() as $applet) {
			$applet->update([
					'column' => $columnHash['column'],
					'row' => array_search($applet->id, $applets)
			]);
		}
		
		$removed = $this->dashboard()->applets()->where('column', $columnHash['column']);
		
		if ($applets) {
			$removed = $removed->whereNotIn('id', $applets);
		}
		
		$this->dashboard()->applets()->where('column', 0)->delete();
		
		$removed->update(['column' => 0]);
	}
	
	public function showSettings($appletId)
	{
		$applet = $this->dashboard()->applets()->find($appletId);
		
		$joint = new $applet->class();
		
		$this->location([__('Edit Applet Settings'), $joint->caption()]);
		
		$form = $this->add(new Form())
		->addElements($joint->elements())
		->confirmLeave()
		->setValues($applet->options);
		
		$form->validate(function(Form $form) use ($applet) {
			$applet->options = $form->getValues();
			
			$applet->save();

			return $form->notify(__('Settings saved!'));
		});
			
		ActionBar::addButton('back');
			
		ActionBar::addButton('save')->on('click', $form->submit());
	}
	
	public function lock()
	{
		$this->locked = true;
		
		return $this;
	}
	
	public function isLocked()
	{
		return $this->locked || ! Auth::user()->can('edit dashboard');
	}
	
	protected function isSingleDashboard()
	{
		return $this->userDashboards()->count() <= 1;
	}
	
	/**
	 * @return Dashboard
	 *
	 * @throws \Symfony\Component\HttpKernel\Exception\HttpException
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	protected function dashboard()
	{
		if (! is_object($this->dashboard)) {
			$this->dashboard = $this->dashboard? Dashboard::find($this->dashboard): $this->defaultUserDashboard();
		}

		return $this->dashboard?: abort(404);
	}
	
	protected function defaultUserDashboard()
	{
		$userDashboard = $this->userDashboards()->orderBy('position')->first();
		
		if (! $userDashboard) {
			$this->lock();
			
			$userDashboard = $this->defaultSystemDashboard();
		}
		
		return $userDashboard;
	}
	
	protected function defaultSystemDashboard()
	{
		return $this->userDashboards(0)->orderBy('position')->first();
	}
	
	protected function userDashboards($userId = null)
	{
		return Dashboard::where(['user_id' => $userId?? $this->userId()]);
	}

	/**
	 * @return number
	 */
	protected function userId()
	{
		return $this->admin? 0: Auth::user()->id;
	}
}
