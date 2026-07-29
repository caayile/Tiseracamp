<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BatchController extends Controller
{
    public function store(Request $request, Program $program): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'quota' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:upcoming,active,completed'],
            'mentor_id' => ['nullable', 'exists:users,id'],
        ]);

        if (! empty($data['mentor_id'])) {
            $program->update(['mentor_id' => $data['mentor_id']]);
        }

        $program->batches()->create(collect($data)->except('mentor_id')->all());

        return back()->with('success', 'Batch dibuat.');
    }
}
