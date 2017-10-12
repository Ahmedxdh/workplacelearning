<?php


namespace App\Http\Controllers;


use App\Cohort;
use App\CompetenceDescription;
use App\EducationProgram;
use App\EducationProgramsService;
use App\Http\Requests\EducationProgram\CreateCompetenceDescriptionRequest;
use App\Http\Requests\EducationProgram\CreateEducationProgramRequest;
use App\Http\Requests\EducationProgram\CreateEntityRequest;
use App\Http\Requests\EducationProgram\DeleteEntityRequest;
use App\Http\Requests\EducationProgram\UpdateEntityRequest;
use App\Http\Requests\EducationProgram\UpdateRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Request;

class EducationProgramsController extends Controller
{
    private $programsService;

    public function __construct(EducationProgramsService $programsService)
    {
        $this->programsService = $programsService;
    }

    public function index()
    {
        return view('pages.education-programs');
    }

    public function getEducationPrograms()
    {
        return EducationProgram::all();
    }

    public function createEducationProgram(CreateEducationProgramRequest $request) {
        $program = $this->programsService->createEducationProgram($request->all());
        return response()->json(["status" => "success", "program" => $program]);
    }

    public function createCohort(EducationProgram $program)
    {
        $cohort = new Cohort();
        $cohort->name = "New cohort";
        $cohort->description = "...";
        $cohort->ep_id = $program->ep_id;
        $cohort->save();

        return response()->json($cohort);
    }

    public function updateCohort(Cohort $cohort, Request $request)
    {
        $cohort->name = $request->get('name');
        $cohort->description = $request->get('description');
        $cohort->save();

        return response()->json($cohort);
    }

    public function getCohort(Cohort $cohort)
    {
        $cohort->load(['competencies', 'timeslots', 'competenceDescription', 'categories', 'resourcePersons'])->get();

        $cohort->canBeDeleted = $cohort->workplaceLearningPeriods()->count() === 0;

        return response()->json($cohort);
    }

    public function deleteCohort(Cohort $cohort)
    {
        if ($cohort->workplaceLearningPeriods()->count() > 0) {
            return response()->json(["status"  => "error",
                                     "message" => \Lang::trans("This cohort has associated workplace learning periods and cannot be deleted"),
            ], 405);
        }

        $cohort->delete();

        return response()->json(["status" => "success"]);
    }

    public function toggleDisabledCohort(Cohort $cohort)
    {
        $cohort->disabled = !$cohort->disabled;
        $cohort->save();

        return response()->json(["status" => "success", "disabled" => $cohort->disabled]);
    }

    public function toggleDisabled(EducationProgram $program)
    {
        $program->disabled = !$program->disabled;
        $program->save();

        return response()->json(["status" => "success", "disabled" => $program->disabled]);
    }

    public function getEditableProgram(EducationProgram $program)
    {

        // Fetch cohorts
        $program->cohorts = $program->cohorts()->with([
            'competencies',
            'timeslots',
            'competenceDescription',
            'categories',
            'resourcePersons',
        ])->get();

        $program->canBeDeleted = $program->cohorts->count() === 0;


        return response()->json($program);

    }

    public function deleteEducationProgram(EducationProgram $program)
    {
        if ($program->cohorts()->count() > 0) {
            return response()->json(["status"  => "error",
                                     "message" => \Lang::trans("This program has cohorts and therefore cannot be deleted"),
            ], 405);
        }
        $program->delete();

        return response()->json(["status" => "success"]);
    }


    /**
     * Create an entity that belongs to a cohort (competence, category etc)
     *
     * @param Cohort $cohort
     * @param CreateEntityRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function createEntity(Cohort $cohort, CreateEntityRequest $request)
    {
        $result = $this->programsService->createEntity($request->get('type'), (string)$request->get('value'),
            $cohort);

        if ($result instanceof Model) {
            return response()->json(["status" => "success", "entity" => $result->toArray()]);
        } else {
            throw new \Exception("Unable to create entity of type {$request->get('type')}");
        }
    }

    /**
     * Delete an entity that belongs to a cohort
     *
     * @param DeleteEntityRequest $request
     * @param $entityId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function deleteEntity(DeleteEntityRequest $request, $entityId)
    {
        try {
            $this->programsService->deleteEntity($entityId, $request->get('type'));
        } catch (QueryException $exception) {
            if (Str::contains($exception->getMessage(), "foreign key constraint fails")) {
                return response()->json([
                    "status"  => "error",
                    "message" => \Lang::trans("This entity is referenced by other entities and cannot be deleted without harming the integrity of student activities."),
                ], 422);
            }
        } catch (\Exception $exception) {
            return response()->json(["status" => "error", "message" => "Unable to delete entity {$entityId}"], 422);
        }

        return response()->json(["status" => "success"]);
    }

    public function updateEntity(UpdateEntityRequest $request, $entityId)
    {
        $entity = $this->programsService->updateEntity((int)$entityId, $request->all());

        $mappedNameField = EducationProgramsService::nameToEntityNameMapping[$request->get('type')];

        return response()->json(["status" => "success", "entity" => $entity, "mappedNameField" => $mappedNameField]);
    }

    public function updateProgram(EducationProgram $program, UpdateRequest $request)
    {
        if (!$this->programsService->updateProgram($program, $request->all())) {
            throw new \Exception("Unable to update program {$program->ep_name}");
        }

        return response()->json(["status" => "success", "program" => $program]);
    }

    public function createCompetenceDescription(Cohort $cohort, CreateCompetenceDescriptionRequest $request)
    {
        $competenceDescription = $this->programsService->handleUploadedCompetenceDescription($cohort,
            $request->get('file'));

        return response()->json(["status" => "success", "competence_description" => $competenceDescription]);
    }

    public function removeCompetenceDescription(Cohort $cohort)
    {
        /** @var CompetenceDescription $competenceDescription */
        $competenceDescription = $cohort->competenceDescription;
        if($competenceDescription !== null) {
            Storage::disk('local')->delete($competenceDescription->file_name);
            $competenceDescription->delete();
        }

        return response()->json(["status" => "success"]);

    }
}