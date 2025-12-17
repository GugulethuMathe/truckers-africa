<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class RoutePlanner extends BaseController
{
    /**
     * Lists all of the driver's saved routes.
     */
    public function index()
    {
        // TODO: Load routes list view using RouteModel
        // return view('routes/index');
    }

    /**
     * Shows the UI for planning a new route.
     */
    public function new()
    {
        // TODO: Load new route planner view
        // return view('routes/new');
    }

    /**
     * Saves a new route and its stops to the database.
     */
    public function create()
    {
        // TODO: Implement route creation logic using RouteModel and RouteStopModel
    }

    /**
     * Shows a specific, previously saved route on the map.
     */
    public function view($id)
    {
        // TODO: Load specific route view using RouteModel and RouteStopModel
        // return view('routes/view');
    }

    /**
     * Manages existing routes - update.
     */
    public function update($id)
    {
        // TODO: Implement route update logic
    }

    /**
     * Manages existing routes - delete.
     */
    public function delete($id)
    {
        // TODO: Implement route deletion logic
    }
}
