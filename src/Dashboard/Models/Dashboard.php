<?php

namespace Epesi\Base\Dashboard\Models;

use atk4\data\Model;
use Epesi\Core\Data\HasEpesiConnection;
use Epesi\Core\System\User\Database\Models\atk4\User;

class Dashboard extends Model
{
    use HasEpesiConnection;
    
    public $table = 'dashboards';
    
    public function init(): void
    {
        parent::init();
        
        $this->addFields([
                'name' => ['type' => 'string'],
                'position' => ['type' => 'integer', 'default' => 0]
        ]);
        
        $this->hasOne('user', [User::class, 'our_field' => 'user_id']);
        
        $this->hasMany('applets', [DashboardApplet::class, 'their_field' => 'dashboard_id']);
        
        $this->addHook('beforeDelete', function($id) {
            $this->withID($id)->ref('applets')->action('delete')->execute();
        });
    }
}
