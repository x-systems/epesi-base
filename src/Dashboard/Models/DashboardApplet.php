<?php

namespace Epesi\Base\Dashboard\Models;

use atk4\data\Model;
use Epesi\Core\Data\HasEpesiConnection;

class DashboardApplet extends Model
{
    use HasEpesiConnection;
    
    public $table = 'dashboard_applets';
    
    public function init()
    {
        parent::init();
        
        $this->addFields([
                'class' => ['type' => 'string'],
                'column' => ['type' => 'integer', 'default' => 1],
                'row' => ['type' => 'integer', 'default' => 0],
                'options' => ['type' => 'array', 'default' => []]
        ]);
        
        $this->hasOne('dashboard', [Dashboard::class, 'our_field' => 'dashboard_id']);
    }
}
