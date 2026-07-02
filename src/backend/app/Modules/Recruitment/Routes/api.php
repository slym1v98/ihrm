<?php
use App\Modules\Recruitment\Infrastructure\Http\Controllers\{RequisitionController,CandidateController,InterviewController,OfferController};
use Illuminate\Support\Facades\Route;
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('/recruitment/requisitions',[RequisitionController::class,'index'])->middleware('permission:recruitment.requisition.view');
    Route::post('/recruitment/requisitions',[RequisitionController::class,'store'])->middleware('permission:recruitment.requisition.create');
    Route::post('/recruitment/requisitions/{id}/submit',[RequisitionController::class,'submit'])->middleware('permission:recruitment.requisition.submit');
    Route::get('/recruitment/candidates',[CandidateController::class,'index'])->middleware('permission:recruitment.candidate.view');
    Route::post('/recruitment/candidates',[CandidateController::class,'store'])->middleware('permission:recruitment.candidate.create');
    Route::patch('/recruitment/candidates/{id}/stage',[CandidateController::class,'updateStage'])->middleware('permission:recruitment.candidate.update');
    Route::get('/recruitment/interviews',[InterviewController::class,'index'])->middleware('permission:recruitment.interview.view');
    Route::post('/recruitment/interviews',[InterviewController::class,'store'])->middleware('permission:recruitment.interview.create');
    Route::post('/recruitment/interviews/{id}/scorecard',[InterviewController::class,'submitScorecard'])->middleware('permission:recruitment.interview.scorecard');
    Route::get('/recruitment/offers',[OfferController::class,'index'])->middleware('permission:recruitment.offer.view');
    Route::post('/recruitment/offers',[OfferController::class,'store'])->middleware('permission:recruitment.offer.create');
    Route::post('/recruitment/offers/{id}/accept',[OfferController::class,'accept'])->middleware('permission:recruitment.offer.accept');
    Route::post('/recruitment/offers/{id}/reject',[OfferController::class,'reject'])->middleware('permission:recruitment.offer.reject');
    Route::post('/recruitment/offers/{id}/convert',[OfferController::class,'convert'])->middleware('permission:recruitment.offer.convert');
});
