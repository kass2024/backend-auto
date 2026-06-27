<?php

namespace App\Http\Controllers\Admin;

use App\Filament\Support\PrintableListRegistry;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ListPrintController extends Controller
{
    public function __invoke(Request $request, string $key): View
    {
        $definition = PrintableListRegistry::get($key);

        abort_if($definition === null, 404);

        return view('filament.print.list', [
            'documentTitle' => $definition['title'],
            'columns' => $definition['columns'],
            'rows' => ($definition['rows'])(),
        ]);
    }
}
