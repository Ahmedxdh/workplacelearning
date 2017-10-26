<?php
namespace App;

use App\WorkplaceLearningPeriod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LearningActivityActing extends Model
{
    // Override the table used for the User Model
    protected $table = 'learningactivityacting';
    // Disable using created_at and updated_at columns
    public $timestamps = false;
    // Override the primary key column
    protected $primaryKey = 'laa_id';

    // Default
    protected $fillable = [
        'laa_id',
        'wplp_id',
        'date',
        'timeslot_id',
        'situation',
        'lessonslearned',
        'support_wp',
        'support_ed',
        'res_person_id',
        'res_material_id',
        'res_material_detail',
        'learninggoal_id'
    ];

    public function learningGoal()
    {
        return $this->hasOne(\App\LearningGoal::class, 'learninggoal_id', 'learninggoal_id');
    }

    public function competence()
    {
        return $this->belongsToMany(\App\Competence::class, 'activityforcompetence', 'learningactivity_id', 'competence_id');
    }

    public function timeslot()
    {
        return $this->hasOne(\App\Timeslot::class, 'timeslot_id', 'timeslot_id');
    }

    public function resourcePerson()
    {
        return $this->hasOne(\App\ResourcePerson::class, 'rp_id', 'res_person_id');
    }

    public function resourceMaterial()
    {
        return $this->hasOne(\App\ResourceMaterial::class, 'rm_id', 'res_material_id');
    }

    public function getTimeslot()
    {
        return $this->timeslot()->first()->timeslot_text;
    }

    public function getResourcePerson()
    {
        return $this->resourcePerson()->first()->person_label;
    }

    public function getResourceMaterial()
    {
        $label = $this->resourceMaterial()->first();
        return ($label) ? $label->rm_label : 'Geen';
    }

    public function getLearningGoal()
    {
        return $this->learningGoal()->first()->learninggoal_label;
    }

    public function getCompetencies()
    {
        return $this->competence()->first();
    }

    public function workplaceLearningPeriod()
    {
        return $this->belongsTo(WorkplaceLearningPeriod::class, 'wplp_id', 'wplp_id');
    }
}
