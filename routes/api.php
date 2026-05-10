<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProblemController;

Route::middleware(['web', 'auth'])->get('/problem-codes', [ProblemController::class, 'problemCodes']);
