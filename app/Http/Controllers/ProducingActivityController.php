<?php
/**
 * This file (ProducingActivityController.php) was created on 06/27/2016 at 16:10.
 * (C) Max Cassee
 * This project was commissioned by HU University of Applied Sciences.
 */

namespace App\Http\Controllers;

use App\Difficulty;
use App\Feedback;
use App\Http\Requests\LearningActivity\ProducingCreateRequest;
use App\Http\Requests\LearningActivity\ProducingUpdateRequest;
use App\LearningActivityProducing;
use App\LearningActivityProducingExportBuilder;
use App\Services\CurrentUserResolver;
use App\Services\Factories\LAPFactory;
use App\Services\LAPUpdater;
use App\Status;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Translation\Translator;

class ProducingActivityController extends Controller
{
    /**
     * @var CurrentUserResolver
     */
    private $currentUserResolver;

    public function __construct(CurrentUserResolver $currentUserResolver)
    {
        $this->currentUserResolver = $currentUserResolver;
    }

    public function show(Request $request, Translator $translator)
    {
        $student = $this->currentUserResolver->getCurrentUser();

        $resourcePersons = $student->currentCohort()->resourcePersons()->get()->merge(
            $student->getCurrentWorkplaceLearningPeriod()->getResourcePersons()
        );

        $categories = $student->currentCohort()->categories()->get()->merge(
            $student->getCurrentWorkplaceLearningPeriod()->categories()->get()
        );

        $exportBuilder = new LearningActivityProducingExportBuilder($student->getCurrentWorkplaceLearningPeriod()->learningActivityProducing()
            ->with('category', 'difficulty', 'status', 'resourcePerson', 'resourceMaterial', 'chain', 'feedback')
            ->take(8)
            ->orderBy('date', 'DESC')
            ->orderBy('lap_id', 'DESC')
            ->get(), $translator);

        $activitiesJson = $exportBuilder->getJson();

        $exportTranslatedFieldMapping = $exportBuilder->getFieldLanguageMapping(app()->make('translator'));

        $wplp = $student->getCurrentWorkplaceLearningPeriod();

        $chains = $wplp->chains;
        $chains->load('activities');

        return view('pages.producing.activity')
            ->with('learningWith', $resourcePersons)
            ->with('categories', $categories)
            ->with('difficulties', Difficulty::all())
            ->with('statuses', Status::all())
            ->with('activitiesJson', $activitiesJson)
            ->with('exportTranslatedFieldMapping', json_encode($exportTranslatedFieldMapping))
            ->with('workplacelearningperiod', $student->getCurrentWorkplaceLearningPeriod())
            ->with('chains', $chains->all());
    }

    public function edit(Request $request, LearningActivityProducing $learningActivityProducing)
    {
        $student = $this->currentUserResolver->getCurrentUser();

        $resourcePersons = $student->currentCohort()->resourcePersons()->get()->merge(
            $student->getCurrentWorkplaceLearningPeriod()->getResourcePersons()
        );

        $categories = $student->currentCohort()->categories()->get()->merge(
            $student->getCurrentWorkplaceLearningPeriod()->categories()->get()
        );

        $wplp = $request->user()->getCurrentWorkplaceLearningPeriod();

        $chains = $wplp->chains;

        return view('pages.producing.activity-edit')
            ->with('activity', $learningActivityProducing)
            ->with('learningWith', $resourcePersons)
            ->with('categories', $categories)
            ->with('chains', $chains);
    }

    public function progress(Translator $translator)
    {
        $student = $this->currentUserResolver->getCurrentUser();
        $activities = $student->getCurrentWorkplaceLearningPeriod()->learningActivityProducing()
            ->with('category', 'difficulty', 'status', 'resourcePerson', 'resourceMaterial')
            ->orderBy('date', 'DESC')
            ->get();
        $exportBuilder = new LearningActivityProducingExportBuilder($activities, $translator);

        $activitiesJson = $exportBuilder->getJson();

        /** @var Carbon $earliest */
        $earliest = null;
        /** @var Carbon $latest */
        $latest = null;

        $activities->each(function (LearningActivityProducing $activity) use (&$earliest, &$latest): void {
            $activityDate = Carbon::createFromTimestamp(strtotime($activity->date));

            if ($earliest === null || $activityDate->lessThan($earliest)) {
                $earliest = $activityDate;
            }
            if ($latest === null || $activityDate->greaterThan($latest)) {
                $latest = $activityDate;
            }
        });

        $exportTranslatedFieldMapping = $exportBuilder->getFieldLanguageMapping(app()->make('translator'));

        $earliest = $earliest ?? Carbon::now();
        $latest = $latest ?? Carbon::now();

        return view('pages.producing.progress')
            ->with('activitiesJson', $activitiesJson)
            ->with('exportTranslatedFieldMapping', json_encode($exportTranslatedFieldMapping))
            ->with('weekStatesDates', ['earliest' => $earliest->format('Y-m-d'), 'latest' => $latest->format('Y-m-d')]);
    }

    public function create(ProducingCreateRequest $request, LAPFactory $LAPManager)
    {
        $learningActivityProducing = $LAPManager->createLAP($request->all());

        $difficulty = $learningActivityProducing->difficulty;
        $status = $learningActivityProducing->status;

        if ($status->isBusy() && ($difficulty->isHard() || $difficulty->isAverage())) {
            // Create Feedback object and redirect
            $feedback = new Feedback();
            $feedback->learningActivityProducing()->associate($learningActivityProducing);
            $feedback->save();

            return redirect()
                ->route('feedback-producing', ['id' => $feedback->fb_id])
                ->with('notification', __('notifications.feedback-hard'));
        }

        return redirect()
            ->route('process-producing')
            ->with('success', __('activity.saved-successfully'));
    }

    public function update(ProducingUpdateRequest $request, LearningActivityProducing $learningActivityProducing, LAPUpdater $LAPUpdater)
    {
        $LAPUpdater->update($learningActivityProducing, $request->all());

        return redirect()->route('process-producing')->with('success', __('activity.saved-successfully'));
    }

    public function delete(LearningActivityProducing $learningActivityProducing)
    {
        $learningActivityProducing->feedback()->delete();
        $learningActivityProducing->delete();

        return redirect()->route('process-producing');
    }
}
