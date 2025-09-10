<?php
namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Priority;
use Illuminate\Http\Request;

class PriorityController extends Controller
{
    public function __invoke(Request $request)
    {
        return Priority::all()->toResourceCollection();
    }
}
