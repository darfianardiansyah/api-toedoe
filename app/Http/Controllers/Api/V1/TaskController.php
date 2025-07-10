<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use Illuminate\Contracts\Cache\Store;
use App\Http\Requests\StoreTaskRequest;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // return TaskResource::collection(Task::all());
        return Task::all()->toResourceCollection();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request)
    {
        $task = Task::create($request->validated());

        return $task->toResource();
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        // return new TaskResource($task);
        // return TaskResource::make($task);
        return $task->toResource();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        //
    }
}
