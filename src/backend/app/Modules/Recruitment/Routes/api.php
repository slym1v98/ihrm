<?php

use App\Modules\Recruitment\Infrastructure\Http\Controllers\Actions\{
    AcceptOfferController,
    ConvertOfferController,
    ListCandidateController,
    ListInterviewController,
    ListOfferController,
    ListRequisitionController,
    RejectOfferController,
    StoreCandidateController,
    StoreInterviewController,
    StoreOfferController,
    StoreRequisitionController,
    SubmitRequisitionController,
    SubmitScorecardInterviewController,
    UpdateStageCandidateController,
};
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('/recruitment/requisitions',ListRequisitionController::class)->middleware('permission:recruitment.requisition.view');
    Route::post('/recruitment/requisitions',StoreRequisitionController::class)->middleware('permission:recruitment.requisition.create');
    Route::post('/recruitment/requisitions/{id}/submit',SubmitRequisitionController::class)->middleware('permission:recruitment.requisition.submit');
    Route::get('/recruitment/candidates',ListCandidateController::class)->middleware('permission:recruitment.candidate.view');
    Route::post('/recruitment/candidates',StoreCandidateController::class)->middleware('permission:recruitment.candidate.create');
    Route::patch('/recruitment/candidates/{id}/stage',UpdateStageCandidateController::class)->middleware('permission:recruitment.candidate.update');
    Route::get('/recruitment/interviews',ListInterviewController::class)->middleware('permission:recruitment.interview.view');
    Route::post('/recruitment/interviews',StoreInterviewController::class)->middleware('permission:recruitment.interview.create');
    Route::post('/recruitment/interviews/{id}/scorecard',SubmitScorecardInterviewController::class)->middleware('permission:recruitment.interview.scorecard');
    Route::get('/recruitment/offers',ListOfferController::class)->middleware('permission:recruitment.offer.view');
    Route::post('/recruitment/offers',StoreOfferController::class)->middleware('permission:recruitment.offer.create');
    Route::post('/recruitment/offers/{id}/accept',AcceptOfferController::class)->middleware('permission:recruitment.offer.accept');
    Route::post('/recruitment/offers/{id}/reject',RejectOfferController::class)->middleware('permission:recruitment.offer.reject');
    Route::post('/recruitment/offers/{id}/convert',ConvertOfferController::class)->middleware('permission:recruitment.offer.convert');
});
